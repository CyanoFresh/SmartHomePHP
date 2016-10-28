<?php

namespace app\servers;

use app\models\Board;
use app\models\History;
use app\models\Item;
use app\models\User;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use Yii;
use yii\base\NotSupportedException;
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
     * All connected users
     * @var ConnectionInterface[]
     */
    protected $user_clients;

    /**
     * All connected boards
     * @var ConnectionInterface[]
     */
    protected $board_clients;

    /**
     * @var array
     */
    protected $item_values;

    /**
     * @var Item[]
     */
    protected $items;

    /**
     * @var array
     */
    protected $isConnectedTimers;

    /**
     * @var array
     */
    protected $awaitingPing;

    /**
     * Class constructor.
     *
     * Init variables, etc.
     *
     * @param LoopInterface $loop
     */
    public function __construct($loop)
    {
        // Init variables
        $this->loop = $loop;
        $this->user_clients = [];
        $this->board_clients = [];
        $this->item_values = [];
        $this->items = [];

        // Database driver hack: Prevent MySQL for disconnecting by timeout
        Yii::$app->db->createCommand('SET SESSION wait_timeout = 2147483;')->execute();
        $this->loop->addPeriodicTimer(8600, function () {
            Yii::$app->db->createCommand('SHOW TABLES;')->execute();
        });

        /** @var Item[] $items */
        $items = Item::find()->asArray()->all();

        foreach ($items as $item) {
            switch ($item['type']) {
                case Item::TYPE_SWITCH:
                case Item::TYPE_VARIABLE_BOOLEAN:
                case Item::TYPE_VARIABLE_BOOLEAN_DOOR:
                    $item['value'] = false;

                    $this->items[$item['id']] = $item;

                    break;
                case Item::TYPE_VARIABLE_TEMPERATURE:
                case Item::TYPE_VARIABLE_HUMIDITY:
                    $item['value'] = 0;

                    $this->items[$item['id']] = $item;

                    break;
                case Item::TYPE_RGB:
                    $item['value'] = [
                        'red' => 0,
                        'green' => 0,
                        'blue' => 0,
                    ];

                    $this->items[$item['id']] = $item;

                    break;
            }
//
//            if ($item->save_history_interval > 0) {
//                $this->loop->addPeriodicTimer($item->save_history_interval, function () use ($item) {
//                    $this->saveHistory($item);
//                });
//            }
        }

        $this->log('Server started');
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->log('Managing new connection...');

        $query = $conn->WebSocket->request->getQuery();

        $type = $query->get('type');

        switch ($type) {
            case 'user':
                $this->log('Connection type is User. Authenticating...');

                $userID = $query->get('id');
                $userAuthKey = $query->get('auth_key');

                if (!$userID or !$userAuthKey) {
                    return $conn->close();
                }

                $user = User::findOne([
                    'id' => $userID,
                    'auth_key' => $userAuthKey,
                ]);

                if (!$user) {
                    return $conn->close();
                }

                // Close previous connection
                if (isset($this->user_clients[$user->id])) {
                    $this->user_clients[$user->id]->close();
                }

                // Attach to users
                $conn->User = $user;
                $this->user_clients[$user->id] = $conn;

                $conn->send(Json::encode([
                    'type' => 'init',
                    'items' => $this->items,
                ]));

                return $this->log("Connected user [{$user->id}] {$user->username}");
            case 'board':
                $this->log('Connection type is Board. Authenticating...');

                $boardID = $query->get('id');
                $boardSecret = $query->get('secret');

                if (!$boardID or !$boardSecret) {
                    $this->log('Wrong login data!');
                    return $conn->close();
                }

                $board = Board::findOne([
                    'id' => $boardID,
                    'type' => Board::TYPE_WEBSOCKET,
                    'secret' => $boardSecret,
                ]);

                if (!$board) {
                    $this->log('Not found!');
                    return $conn->close();
                }

                // Attach to boards
                $conn->Board = $board;
                $this->board_clients[$board->id] = $conn;

                $this->isConnectedTimers[$board->id] = $this->loop->addPeriodicTimer(180, function () use ($board) {
                    $this->log("Checking for timeout [$board->id] board...");

                    if (isset($this->awaitingPing[$board->id])) {
                        $this->log("There was no response from last ping! Disconnecting...");

                        return $this->board_clients[$board->id]->close();
                    }

                    $this->log("Sending ping command...");

                    $this->awaitingPing[$board->id] = true;

                    return $this->sendToBoard($board->id, [
                        'type' => 'ping',
                    ]);
                });

                return $this->log("Connected board [{$board->id}]");
            case 'api_user':
                $this->log('Connection type is API User. Authenticating...');

                $userID = $query->get('id');
                $userAuthKey = $query->get('auth_key');

                if (!$userID or !$userAuthKey) {
                    return $conn->close();
                }

                $user = User::findOne([
                    'id' => $userID,
                    'auth_key' => $userAuthKey,
                ]);

                if (!$user) {
                    return $conn->close();
                }

                $conn->api_user = true;
                $conn->User = $user;

                return $this->log("Connected API User [{$user->id}]");
        }

        return $this->log('Connection has unknown type. Disconnect');
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->log('Message received: ' . $msg);

        if (isset($from->User)) {
            return $this->handleUserMessage($from, $msg);
        } elseif (isset($from->Board)) {
            return $this->handleBoardMessage($from, $msg);
        }

        return false;
    }

    public function onClose(ConnectionInterface $conn)
    {
        if (isset($conn->User)) {
            if (!$conn->api_user) {
                unset($this->user_clients[$conn->User->id]);
            }

            $conn->User->generateAuthKey();
            $conn->User->save();

            $this->log("User [{$conn->User->id}] disconnected");
        } elseif (isset($conn->Board)) {
            unset($this->board_clients[$conn->Board->id]);

            // Remove timeout timer
            if (isset($this->isConnectedTimers[$conn->Board->id])) {
                $this->isConnectedTimers[$conn->Board->id]->cancel();
                unset($this->isConnectedTimers[$conn->Board->id]);
            }
            if (isset($this->awaitingPing[$conn->Board->id])) {
                unset($this->awaitingPing[$conn->Board->id]);
            }

            $this->log("Board [{$conn->Board->id}] disconnected");
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->log("Error: {$e->getMessage()} in file {$e->getFile()} at line {$e->getLine()}");

        // Close connection
        $conn->close();
    }

    /**
     * @param $from
     * @param $msg
     * @return bool
     */
    public function handleUserMessage($from, $msg)
    {
        $user = $from->User;
        $data = Json::decode($msg);

        switch ($data['type']) {
            case 'turnON':
                return $this->handleTurnOn($from, $user, $data);
            case 'turnOFF':
                return $this->handleTurnOff($from, $user, $data);
            case 'rgb':
                return $this->handleRgb($from, $user, $data);
        }

        return false;
    }

    /**
     * @param $from
     * @param $msg
     * @return bool
     */
    public function handleBoardMessage($from, $msg)
    {
        /** @var Board $board */
        $board = $from->Board;
        $data = Json::decode($msg);

        switch ($data['type']) {
            case 'value':
                $value = $data['value'];
                $pin = (integer)$data['pin'];

                $item = Item::findOne([
                    'board_id' => $board->id,
                    'pin' => $pin,
                ]);

                if (!$item) {
                    return $this->log('Trying to use unknown item');
                }

                if (in_array($item->type, [
                    Item::TYPE_SWITCH,
                    Item::TYPE_VARIABLE_BOOLEAN,
                    Item::TYPE_VARIABLE_BOOLEAN_DOOR,
                ])) {
                    $value = $this->valueToBoolean($data['value']);
                }

                if ($item->type === Item::TYPE_VARIABLE_BOOLEAN_DOOR) {
                    curl_setopt_array($ch = curl_init(), array(
                        CURLOPT_URL => 'https://pushall.ru/api.php',
                        CURLOPT_POSTFIELDS => array(
                            'type' => 'self',
                            'id' => Yii::$app->params['pushAllID'],
                            'key' => Yii::$app->params['pushAllKey'],
                            'text' => $value ? 'Дверь открыта' : 'Дверь закрыта',
                            'title' => 'Сигнализация на двери'
                        ),
                        CURLOPT_RETURNTRANSFER => true
                    ));
                    $return = curl_exec($ch); //получить ответ или ошибку
                    curl_close($ch);

                    $this->log($return);
                }

                $this->items[$item->id]['value'] = $value;

                $this->sendUsers([
                    'type' => 'value',
                    'item_id' => $item->id,
                    'item_type' => $item->type,
                    'value' => $value,
                ]);

                // Save to history
                $history = new History();
                $history->item_id = $item->id;
                $history->commited_at = time();
                $history->value = $value;
                $history->save();

                break;
            case 'values':
                unset($data['type']);

                foreach ($data as $pin => $value) {
                    $item = Item::findOne([
                        'board_id' => $board->id,
                        'pin' => $pin,
                    ]);

                    if (!$item) {
                        return $this->log('Trying to use unknown item');
                    }

                    if (in_array($item->type, [
                        Item::TYPE_SWITCH,
                        Item::TYPE_VARIABLE_BOOLEAN,
                        Item::TYPE_VARIABLE_BOOLEAN_DOOR,
                    ])) {
                        $value = $value === 0 ? false : true;
                    }

                    $this->items[$item->id]['value'] = $value;

                    $this->sendUsers([
                        'type' => 'value',
                        'item_id' => $item->id,
                        'item_type' => $item->type,
                        'value' => $value,
                    ]);

                    // Save to history
                    $history = new History();
                    $history->item_id = $item->id;
                    $history->commited_at = time();
                    $history->value = $value;
                    $history->save();
                }

                break;
            case 'pong':
                $this->log("Removing board [$board->id] from timeout queue");

                if (isset($this->awaitingPing[$board->id])) {
                    unset($this->awaitingPing[$board->id]);
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
     * @throws NotSupportedException
     */
    protected function handleTurnOn(ConnectionInterface $from, $user, $data)
    {
        $item_id = (int)$data['item_id'];

        $item = Item::findOne($item_id);

        if (!$item) {
            return $from->send([
                'type' => 'error',
                'message' => 'Такое устройство не существует',
            ]);
        }

        if ($item->type !== Item::TYPE_SWITCH) {
            return $from->send([
                'type' => 'error',
                'message' => 'Данный тип устройства нельзя переключать',
            ]);
        }

        $board = $item->board;

        switch ($board->type) {
            case Board::TYPE_AREST:
                throw new NotSupportedException();

                break;
            case Board::TYPE_WEBSOCKET:
                if (!$this->isBoardConnected($board->id)) {
                    return $from->send(Json::encode([
                        'type' => 'error',
                        'message' => 'Устройство не подключено',
                    ]));
                }

                $this->sendToBoard($board->id, [
                    'type' => 'turnON',
                    'pin' => $item->pin,
                ]);

                break;
        }

        return true;
    }

    /**
     * @param ConnectionInterface $from
     * @param User $user
     * @param array $data
     * @return bool|mixed
     * @throws NotSupportedException
     */
    protected function handleTurnOff(ConnectionInterface $from, $user, $data)
    {
        $item_id = (int)$data['item_id'];

        $item = Item::findOne($item_id);

        if (!$item) {
            return $from->send([
                'type' => 'error',
                'message' => 'Такое устройство не существует',
            ]);
        }

        if ($item->type !== Item::TYPE_SWITCH) {
            return $from->send([
                'type' => 'error',
                'message' => 'Данный тип устройства нельзя переключать',
            ]);
        }

        $board = $item->board;

        switch ($board->type) {
            case Board::TYPE_AREST:
                throw new NotSupportedException();

                break;
            case Board::TYPE_WEBSOCKET:
                if (!$this->isBoardConnected($board->id)) {
                    return $from->send(Json::encode([
                        'type' => 'error',
                        'message' => 'Устройство не подключено',
                    ]));
                }

                $this->sendToBoard($board->id, [
                    'type' => 'turnOFF',
                    'pin' => $item->pin,
                ]);

                break;
        }

        return false;
    }

    /**
     * @param ConnectionInterface $from
     * @param User $user
     * @param array $data
     * @return bool|mixed
     * @throws NotSupportedException
     */
    private function handleRgb($from, $user, $data)
    {
        $item_id = (int)$data['item_id'];
        $item = Item::findOne($item_id);

        if (!$item) {
            return $from->send([
                'type' => 'error',
                'message' => 'Такое устройство не существует',
            ]);
        }

        if ($item->type !== Item::TYPE_RGB) {
            return $from->send([
                'type' => 'error',
                'message' => 'Данный тип устройства не является RGB',
            ]);
        }

        $red = $data['red'];
        $green = $data['green'];
        $blue = $data['blue'];

        if ($red > 255) {
            $red = 255;
        }

        if ($green > 255) {
            $green = 255;
        }

        if ($blue > 255) {
            $blue = 255;
        }

        $board = $item->board;

        switch ($board->type) {
            case Board::TYPE_AREST:
                throw new NotSupportedException();

                break;
            case Board::TYPE_WEBSOCKET:
                if (!$this->isBoardConnected($board->id)) {
                    return $from->send(Json::encode([
                        'type' => 'error',
                        'message' => 'Устройство не подключено',
                    ]));
                }

                $this->sendToBoard($board->id, [
                    'type' => 'rgb',
                    'red' => $red * 4,
                    'green' => $green * 4,
                    'blue' => $blue * 4,
                ]);

                break;
        }

        $this->items[$item->id]['value'] = [
            'red' => $red,
            'green' => $green,
            'blue' => $blue,
        ];

        $this->sendUsers([
            'type' => 'value',
            'item_id' => $item->id,
            'red' => $red,
            'green' => $green,
            'blue' => $blue,
        ]);

        return false;
    }

    /**
     * Send data to all users
     *
     * @param array $data
     */
    private function sendUsers($data)
    {
        $msg = Json::encode($data);

        foreach ($this->user_clients as $client) {
            $client->send($msg);
        }
    }

    /**
     * Send data to specific board
     *
     * @param integer $board_id
     * @param array $data
     * @return bool|ConnectionInterface
     */
    private function sendToBoard($board_id, $data)
    {
        if (isset($this->board_clients[$board_id])) {
            /** @var ConnectionInterface $client */
            $client = $this->board_clients[$board_id];

            $msg = Json::encode($data);

            $this->log("Sending to board [$board_id]: $msg");

            return $client->send($msg);
        }

        $this->log("Cannot send to board [$board_id]: not connected");

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
     * @param integer $boardID
     * @return bool
     */
    private function isBoardConnected($boardID)
    {
        return isset($this->board_clients[$boardID]);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function valueToBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 0 ? false : true;
        }

        return (bool)$value;
    }
}
