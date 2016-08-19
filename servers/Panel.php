<?php

namespace app\servers;

use app\components\ApiHelper;
use app\models\History;
use app\models\Item;
use app\models\User;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\Version\RFC6455\Connection;
use React\EventLoop\LoopInterface;
use Yii;
use yii\helpers\Json;

/**
 * Class Panel
 *
 * WebSockets handler
 *
 * @package app\components
 * @author CyanoFresh <cyanofresh@gmail.com>
 */
class Panel implements MessageComponentInterface
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var ConnectionInterface[]
     */
    protected $clients;

    /**
     * @var array
     */
    protected $items;

    /**
     * Panel constructor.
     *
     * Init variables and preparing
     *
     * @param LoopInterface $loop
     */
    public function __construct($loop)
    {
        $this->loop = $loop;
        $this->clients = [];
        $this->items = [];

        // Database driver hack
        Yii::$app->db->createCommand('SET SESSION wait_timeout = 2147483;')->execute();

        // Prevent MySQL for disconnecting by timeout
        $this->loop->addPeriodicTimer(8600, function () {
            Yii::$app->db->createCommand('SHOW TABLES;')->execute();
        });

        /** @var Item[] $items */
        $items = Item::find()->all();

        foreach ($items as $item) {
            $this->items[$item->id] = $item;

            if ($item->save_history_interval > 0) {
                $this->loop->addPeriodicTimer($item->save_history_interval, function () use ($item) {
                    $this->saveHistory($item);
                });
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function onOpen(ConnectionInterface $conn)
    {
        // Get query
        $query = $conn->WebSocket->request->getQuery();

        $id = $query->get('id');
        $time = $query->get('time');
        $token = $query->get('token');

        if ((time() - $time) >= Yii::$app->params['auth']['tokenExpireSec']) {
            return $this->log("Auth failed (Token expired). ID: $id; Token: $token; IP: {$conn->remoteAddress}; Timestamp: $time");
        }

        // Find user by auth info
        /** @var User $user */
        $user = User::findOne([
            'id' => $id,
            'auth_key' => $token,
        ]);

        // Security checks
        if (!$user) {
            return $this->log("Auth failed. ID: $id; Token: $token; IP: {$conn->remoteAddress}; Timestamp: $time");
        }

        // Close previous connection
        if (isset($this->clients[$user->id])) {
            $this->clients[$user->id]->close();
        }

        // Attach to online users
        $conn->User = $user;
        $this->clients[$user->id] = $conn;

        return $this->log("Connected new user [$id] {$user->username}");
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if (!$from->User) {
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => 'Необходима авторизаия',
            ]));
        }

        /** @var User $user */
        $user = $from->User;
        $data = Json::decode($msg);

        if ($data['type'] === 'switch') {
            return $this->handleSwitch($from, $user, $data);
        }

        return false;
    }

    public function onClose(ConnectionInterface $conn)
    {
        if (isset($conn->User)) {
            unset($this->clients[$conn->User->id]);

            // Regenerate auth key
            $conn->User->generateAuthKey();
            $conn->User->save();
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // Logging
        echo "Error: {$e->getMessage()} in file {$e->getFile()} at line {$e->getLine()}" . PHP_EOL;

        // Close connection
        $conn->close();
    }

    /**
     * @param ConnectionInterface $from
     * @param User $user
     * @param array $data
     * @return bool|mixed
     */
    protected function handleTurnOn(ConnectionInterface $from, $user, $data)
    {
        $item_id = (int)$data['item_id'];

        $item = Item::findOne($item_id);

        if (!$item) {
            return $from->send([
                'type' => 'error',
                'message' => 'Включить не удалось',
            ]);
        }

        if ($item->type !== Item::TYPE_SWITCH) {
            return $from->send([
                'type' => 'error',
                'message' => 'Данный тип устройства нельзя переключать',
            ]);
        }

        $api = new ApiHelper($item);
        $api->turnOn();

        return $this->saveHistory($item, Item::VALUE_ON);
    }

    /**
     * Send data to all users
     *
     * @param array $data
     */
    private function sendAll($data)
    {
        $encodedData = Json::encode($data);

        foreach ($this->clients as $client) {
            $client->send($encodedData);
        }
    }

    /**
     * Send data to specific user
     *
     * @param integer $user_id
     * @param array $data
     * @return bool|mixed
     */
    private function sendTo($user_id, $data)
    {
        if (isset($this->clients[$user_id])) {
            /** @var ConnectionInterface $client */
            $client = $this->clients[$user_id];

            return $client->send(Json::encode($data));
        }

        return false;
    }

    /**
     * @param string $message
     */
    private function log($message)
    {
        echo $message . PHP_EOL;
    }

    /**
     * Save to history Item value. Returns true if saved
     *
     * @param Item $item
     * @param mixed|null $value
     * @return bool
     */
    private function saveHistory($item, $value = null)
    {
        if ($value === null) {
            try {
                $api = new ApiHelper($item);
                $value = $api->getValue();
            } catch (\Exception $e) {
                return false;
            }
        }

        $history = new History();
        $history->item_id = $item->id;
        $history->commited_at = time();
        $history->value = $value;

        return $history->save();
    }
}
