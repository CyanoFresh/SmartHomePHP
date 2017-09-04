<?php

namespace app\modules\server\components;

use app\models\Device;
use app\models\User;
use app\modules\server\events\ConnectionErrorEvent;
use app\modules\server\events\ConnectionEvent;
use app\modules\server\events\ConnectionMessageEvent;
use app\modules\server\events\DeviceAuthEvent;
use app\modules\server\events\ServerEvent;
use app\modules\server\events\UserAuthEvent;
use Guzzle\Http\QueryString;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\Version\RFC6455\Connection;
use yii\base\InvalidParamException;
use yii\helpers\Json;
use yii\helpers\VarDumper;

class CoreServer extends BaseServer
{
    const EVENT_INIT = 'core_server.init';
    const EVENT_CONNECTION_OPEN = 'core_server.connection.open';
    const EVENT_CONNECTION_CLOSE = 'core_server.connection.close';
    const EVENT_CONNECTION_ERROR = 'core_server.connection.error';
    const EVENT_CONNECTION_MESSAGE = 'core_server.connection.message';
    const EVENT_AUTH_USER = 'core_server.auth.user';
    const EVENT_AUTH_DEVICE = 'core_server.auth.device';
    const EVENT_AUTH_ERROR = 'core_server.auth.error';

    /**
     * @var ConnectionInterface[]
     */
    public $deviceClients = [];

    /**
     * @var array
     */
    public $userClients = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Doing general magic...

        $this->trigger(self::EVENT_INIT, new ServerEvent([
            'server' => $this,
        ]));
    }

    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $connection The socket/connection that just connected to your application
     * @throws \Exception
     */
    public function onOpen(ConnectionInterface $connection)
    {
        if (!$this->authenticate($connection)) {
            return;
        }

        $this->trigger(self::EVENT_CONNECTION_OPEN, new ConnectionEvent([
            'server' => $this,
            'connection' => $connection,
        ]));
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $connection The socket/connection that is closing/closed
     * @throws \Exception
     */
    public function onClose(ConnectionInterface $connection)
    {
        if (isset($connection->user) && isset($this->userClients[$connection->user->id]) && isset($this->userClients[$connection->user->id][$connection->resourceId])) {
            if (count($this->userClients[$connection->user->id]) === 1) {
                unset($this->userClients[$connection->user->id]);
            } else {
                unset($this->userClients[$connection->user->id][$connection->resourceId]);
            }
        } elseif (isset($connection->device) && isset($this->deviceClients[$connection->device->id])) {
            unset($this->deviceClients[$connection->device->id]);
        }

        $this->trigger(self::EVENT_CONNECTION_CLOSE, new ConnectionEvent([
            'server' => $this,
            'connection' => $connection,
        ]));
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->trigger(self::EVENT_CONNECTION_ERROR, new ConnectionErrorEvent([
            'server' => $this,
            'connection' => $conn,
            'exception' => $e,
        ]));
    }

    /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string $msg The message received
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->trigger(self::EVENT_CONNECTION_MESSAGE, new ConnectionMessageEvent([
            'server' => $this,
            'connection' => $from,
            'message' => $msg,
        ]));
    }

    /**
     * @param Connection $connection
     * @return bool
     */
    protected function authenticate(Connection $connection)
    {
        /** @var QueryString $query */
        $query = $connection->WebSocket->request->getQuery();

        $type = $query->get('type');

        switch ($type) {
            case 'user':
                return $this->authUser($connection, $query);
            case 'device':
                return $this->authDevice($connection, $query);
            default:
                throw new InvalidParamException('Unknown connection type');
        }
    }

    /**
     * @param Connection $connection
     * @param QueryString $query
     * @return bool
     */
    protected function authUser(Connection $connection, QueryString $query)
    {
        $authToken = (string)$query->get('auth_token');
        $apiKey = (string)$query->get('api_key');

        if (!$authToken && !$apiKey) {
            $connection->close();

            throw new InvalidParamException('No token or API key provided');
        }

        $ip = $connection->remoteAddress;
        $origin = $connection->WebSocket->request->getHeader('Origin');

        $isInternal = $apiKey !== null && $ip === '127.0.0.1' && $origin === 'api';

        if ($isInternal) {
            $user = User::findOne([
                'api_key' => $apiKey,
            ]);
        } else {
            $user = User::findOne([
                'auth_token' => $authToken,
            ]);
        }

        if (!$user) {
            $connection->close();

            $this->trigger(self::EVENT_AUTH_ERROR, new UserAuthEvent([
                'connection' => $connection,
                'server' => $this,
                'user' => $user,
                'data' => [
                    'authToken' => $authToken,
                    'apiKey' => $apiKey,
                    'ip' => $ip,
                    'origin' => $origin,
                ],
            ]));

            return false;
        }

        $user->reGenerateAuthToken();

        $connection->isInternal = $isInternal;
        $connection->user = $user;

        $this->userClients[$user->id] = [];
        $this->userClients[$user->id][$connection->resourceId] = $connection;

        $this->trigger(self::EVENT_AUTH_USER, new UserAuthEvent([
            'connection' => $connection,
            'server' => $this,
            'user' => $user,
            'data' => [
                'authToken' => $authToken,
                'apiKey' => $apiKey,
                'ip' => $ip,
                'origin' => $origin,
            ],
        ]));

        return true;
    }

    /**
     * @param Connection $connection
     * @param QueryString $query
     * @return bool
     */
    protected function authDevice(Connection $connection, QueryString $query)
    {
        $deviceKey = (string)$query->get('key');

        $isLocal = $connection->remoteAddress === '127.0.0.1';

        if (!$deviceKey) {
            $connection->close();

            throw new InvalidParamException('No device key provided');
        }

        $device = Device::findOne([
            'key' => $deviceKey,
        ]);

        if (!$device || (!$isLocal && !$device->allow_remote_connection)) {
            $connection->close();

            $this->trigger(self::EVENT_AUTH_ERROR, new DeviceAuthEvent([
                'connection' => $connection,
                'server' => $this,
                'device' => $device,
                'data' => [
                    'authToken' => $deviceKey,
                    'apiKey' => $isLocal,
                    'ip' => $connection->remoteAddress,
                ],
            ]));
        }

        $connection->device = $device;

        $this->deviceClients[$device->id] = $connection;

        $this->trigger(self::EVENT_AUTH_DEVICE, new DeviceAuthEvent([
            'connection' => $connection,
            'server' => $this,
            'device' => $device,
            'data' => [
                'authToken' => $deviceKey,
                'apiKey' => $isLocal,
                'ip' => $connection->remoteAddress,
            ],
        ]));

        return true;
    }

    /**
     * @param object|array|string $data
     */
    public function sendUsers($data)
    {
        $msg = $this->encodeSendData($data);

        foreach ($this->userClients as $client) {
            /** @var Connection $connection */
            foreach ($client as $connection) {
                $connection->send($msg);
            }
        }
    }

    /**
     * @param int $userId
     * @param object|array|string $data
     */
    public function sendUser(int $userId, $data)
    {
        $msg = $this->encodeSendData($data);

        if (isset($this->userClients[$userId]) && count($this->userClients[$userId]) > 0) {
            /** @var Connection $connection */
            foreach ($this->userClients[$userId] as $connection) {
                $connection->send($msg);
            }
        }
    }

    /**
     * @param int $deviceId
     * @param object|array|string $data
     */
    public function sendDevice(int $deviceId, $data)
    {
        $msg = $this->encodeSendData($data);

        if (isset($this->deviceClients[$deviceId])) {
            $this->deviceClients[$deviceId]->send($msg);
        }
    }

    /**
     * @param object|array|string $data
     */
    public function sendDevices($data)
    {
        $msg = $this->encodeSendData($data);

        foreach ($this->deviceClients as $connection) {
            $connection->send($msg);
        }
    }

    /**
     * @param object|array|string $data
     * @return string
     */
    public function encodeSendData($data)
    {
        $msg = $data;

        if (is_array($data) || is_object($data)) {
            $msg = Json::encode($data);
        }

        return $msg;
    }
}
