<?php

namespace app\servers;

use app\components\ApiHelper;
use app\models\Board;
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
    protected $user_clients;

    /**
     * @var ConnectionInterface[]
     */
    protected $board_clients;

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
        $this->user_clients = [];
        $this->board_clients = [];
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

        $type = $query->get('type');

        switch ($type) {
            case 'user':
                $userID = $query->get('id');
                $userAuthKey = $query->get('auth_key');

                if (!$userID or !$userAuthKey) {
                    return false;
                }

                $user = User::findOne([
                    'id' => $userID,
                    'auth_key' => $userAuthKey,
                ]);

                if (!$user) {
                    return false;
                }

                // Close previous connection
                if (isset($this->user_clients[$user->id])) {
                    $this->user_clients[$user->id]->close();
                }

                // Attach to clients
                $conn->User = $user;
                $this->user_clients[$user->id] = $conn;

                return $this->log("Connected user [{$user->id}] {$user->username}");
            case 'board':
                $boardID = $query->get('id');
                $boardSecret = $query->get('secret');

                if (!$boardID or !$boardSecret) {
                    return false;
                }

                $board = Board::findOne([
                    'id' => $boardID,
                    'type' => Board::TYPE_WEBSOCKET,
                    'secret' => $boardSecret,
                ]);

                if (!$board) {
                    return false;
                }

                // Close previous connection
                if (isset($this->board_clients[$board->id])) {
                    $this->board_clients[$board->id]->close();
                }

                // Attach to clients
                $conn->Board = $board;
                $this->board_clients[$board->id] = $conn;

                return $this->log("Connected board [{$board->id}]");
        }

        return false;
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if ($from->User) {
            return $this->handleUserMessage($from, $msg);
        } elseif ($from->Board) {
            return $this->handleBoardMessage($from, $msg);
        }

        return false;
    }

    public function onClose(ConnectionInterface $conn)
    {
        if (isset($conn->User)) {
            unset($this->user_clients[$conn->User->id]);
        } elseif (isset($conn->Board)) {
            unset($this->board_clients[$conn->Board->id]);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // Logging
        echo "Error: {$e->getMessage()} in file {$e->getFile()} at line {$e->getLine()}" . PHP_EOL;

        // Close connection
        $conn->close();
    }

    public function handleUserMessage($from, $msg)
    {
        $user = $from->User;
        $data = Json::decode($msg);

        switch ($data['type']) {
            case 'switch':
                $item_id = $data['item_id'];
                $item = Item::findOne($item_id);

                if (!$item) {
                    return false;
                }

                $board = $item->board;

                if ($board->type === Board::TYPE_WEBSOCKET) {
                    if (isset($this->board_clients[$board->id])) {
                        $this->board_clients[$board->id]->send(Json::encode([
                            'type' => 'switch',
                            'pin' => $item->pin,
                        ]));
                    }
                }

                break;
        }

        return false;
    }

    public function handleBoardMessage($from, $msg)
    {
        $board = $from->Board;
        $data = Json::decode($msg);

        switch ($data['type']) {
            case 'value':
                $pin = $data['pin'];

                $item = Item::findOne([
                    'board_id' => $board->id,
                    'pin' => $pin,
                ]);

                if (!$item) {
                    return false;
                }

                break;
        }

        return false;
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

        foreach ($this->user_clients as $client) {
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
        if (isset($this->user_clients[$user_id])) {
            /** @var ConnectionInterface $client */
            $client = $this->user_clients[$user_id];

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
