<?php

namespace app\servers;

use app\models\Board;
use app\models\Event;
use app\models\History;
use app\models\Item;
use app\models\TaskAction;
use app\models\User;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
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
     * @var Item[]
     */
    protected $items;

    /**
     * @var TimerInterface[]
     */
    protected $isConnectedTimers;

    /**
     * @var array
     */
    protected $awaitingPong;

    /**
     * @var TimerInterface[]
     */
    protected $eventTimers;

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
                        0,
                        0,
                        0,
                    ];

                    $this->items[$item['id']] = $item;

                    break;
            }
        }

        $this->scheduleEvents();

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

                $this->logUserConnection($user, true);

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

                $this->isConnectedTimers[$board->id] = $this->loop->addTimer(
                    Yii::$app->params['server']['connectionCheckTimeout'],
                    function () use ($board) {
                        return $this->doConnectionCheckTimer($board);
                    }
                );

                $this->logBoardConnection($board, true);

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
        if (isset($from->User)) {
            $this->log("Message: '$msg' from User [{$from->User->id}]");

            return $this->handleUserMessage($from, $msg);
        } elseif (isset($from->Board)) {
            $this->log("Message: '$msg' from Board [{$from->Board->id}]");

            return $this->handleBoardMessage($from, $msg);
        }

        return $this->log("Message: '$msg' from unknown client");
    }

    public function onClose(ConnectionInterface $conn)
    {
        if (isset($conn->User)) {
            if (!$conn->api_user) {
                unset($this->user_clients[$conn->User->id]);
            }

            $conn->User->generateAuthKey();
            $conn->User->save();

            $this->logUserConnection($conn->User, false);

            $this->log("User [{$conn->User->id}] disconnected");
        } elseif (isset($conn->Board)) {
            $boardId = $conn->Board->id;

            $this->log("Disconnecting Board [{$boardId}]...");

            // Remove timer
            if (isset($this->isConnectedTimers[$boardId])) {
                $this->log("Disabling timeout timer...");

                $this->isConnectedTimers[$boardId]->cancel();
                unset($this->isConnectedTimers[$boardId]);

                $this->log("Disabled");
            }

            if (isset($this->awaitingPong[$boardId])) {
                $this->log("Removing from awaiting pong list...");

                unset($this->awaitingPong[$boardId]);

                $this->log("Removed");
            }

            unset($this->board_clients[$boardId]);

            $this->logBoardConnection($boardId, false);

            $this->log("Board [{$boardId}] disconnected");
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
            case 'rgbMode':
                return $this->handleRgbMode($from, $user, $data);
        }

        return $this->log("Unknown command from user: $msg");
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

        if (isset($this->awaitingPong[$board->id])) {
            unset($this->awaitingPong[$board->id]);

            $this->log("Removed board [$board->id] from timeout queue");
        }

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

                // Trig event
                $this->trigItemValueEvent($item, $value);

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

                if ($item->type === Item::TYPE_RGB) {
                    $value = explode(',', $value);

                    // Convert R,G,B from 10 bit to 8 bit
                    for ($i = 0; $i < 3; $i++) {
                        $value[$i] = round($value[$i] / 4);
                    }
                }

                $this->items[$item->id]['value'] = $value;

                $this->sendUsers([
                    'type' => 'value',
                    'item_id' => $item->id,
                    'item_type' => $item->type,
                    'value' => $value,
                ]);

                // Save to history
                $this->logItemValue($item, $value);

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

                    // Trig event
                    $this->trigItemValueEvent($item, $value);

                    if (in_array($item->type, [
                        Item::TYPE_SWITCH,
                        Item::TYPE_VARIABLE_BOOLEAN,
                        Item::TYPE_VARIABLE_BOOLEAN_DOOR,
                    ])) {
                        $value = $value === 0 ? false : true;
                    } elseif ($item->type === Item::TYPE_RGB) {
                        $value = explode(',', $value);

                        // Convert R,G,B from 10 bit to 8 bit
                        for ($i = 0; $i < 3; $i++) {
                            $value[$i] = round($value[$i] / 4);
                        }
                    }

                    $this->items[$item->id]['value'] = $value;

                    $this->sendUsers([
                        'type' => 'value',
                        'item_id' => $item->id,
                        'item_type' => $item->type,
                        'value' => $value,
                    ]);

                    // Save to history
                    $this->logItemValue($item, $value);
                }

                break;
            case 'pong':
                $this->log("Received pong from board [$board->id]");

                break;
            default:
                $this->log("Unknown command: \"{$data['type']}\"");
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

        $this->logSwitch($item, $user, 1);

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

        $this->logSwitch($item, $user, 0);

        return true;
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

                $fade = isset($data['fade']) ? (bool)$data['fade'] : false;

                $this->sendToBoard($board->id, [
                    'type' => 'rgb',
                    'red' => $red * 4,
                    'green' => $green * 4,
                    'blue' => $blue * 4,
                    'fade' => $fade,
                ]);

                break;
        }

        $rgbArray = [
            $red,
            $green,
            $blue
        ];

        $this->items[$item->id]['value'] = $rgbArray;

//        $this->sendUsers([
//            'type' => 'value',
//            'item_id' => $item->id,
//            'item_type' => Item::TYPE_RGB,
//            'value' => $rgbArray,
//        ]);

        $history = new History();
        $history->type = History::TYPE_USER_ACTION;
        $history->user_id = $user->id;
        $history->item_id = $item->id;
        $history->commited_at = time();
        $history->value = $red . ',' . $green . ',' . $blue;

        if (!$history->save()) {
            $this->log("Cannot log: ");
            var_dump($history->errors);
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
    private function handleRgbMode($from, $user, $data)
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

        $mode = $data['mode'];

        if (!in_array($mode, [Item::MODE_BREATH, Item::MODE_RAINBOW])) {
            return $from->send([
                'type' => 'error',
                'message' => 'Неизвестный режим',
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
                    'type' => 'rgbMode',
                    'mode' => $mode,
                ]);

                break;
        }

        $history = new History();
        $history->type = History::TYPE_USER_ACTION;
        $history->user_id = $user->id;
        $history->item_id = $item->id;
        $history->commited_at = time();
        $history->value = $mode;

        if (!$history->save()) {
            $this->log("Cannot log: ");
            var_dump($history->errors);
        }

        return true;
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

    /**
     * @param Board $board
     * @param boolean $connected
     */
    protected function logBoardConnection($board, $connected)
    {
        $model = new History();
        $model->type = History::TYPE_BOARD_CONNECTION;
        $model->board_id = $board->id;
        $model->value = $connected;
        $model->commited_at = time();

        if (!$model->save()) {
            $this->log("Cannot log: ");
            var_dump($model->errors);
        }
    }

    /**
     * @param User $user
     * @param boolean $connected
     */
    protected function logUserConnection($user, $connected)
    {
        $model = new History();
        $model->type = History::TYPE_USER_CONNECTION;
        $model->user_id = $user->id;
        $model->value = $connected;
        $model->commited_at = time();

        if (!$model->save()) {
            $this->log("Cannot log: ");
            var_dump($model->errors);
        }
    }

    /**
     * @param Item $item
     * @param User $user
     * @param boolean $value
     */
    protected function logSwitch($item, $user, $value)
    {
        $model = new History();
        $model->type = History::TYPE_USER_ACTION;
        $model->item_id = $item->id;
        $model->user_id = $user->id;
        $model->value = $value;
        $model->commited_at = time();

        if (!$model->save()) {
            $this->log("Cannot log: ");
            var_dump($model->errors);
        }
    }

    /**
     * @param Item $item
     * @param mixed $value
     */
    protected function logItemValue($item, $value)
    {
        if ($item->type === Item::TYPE_RGB) {
            $value = implode(',', $value);
        }

        $model = new History();
        $model->type = History::TYPE_ITEM_VALUE;
        $model->item_id = $item->id;
        $model->commited_at = time();
        $model->value = $value;

        if (!$model->save()) {
            $this->log("Cannot log: ");
            var_dump($model->errors);
        }
    }

    public function doConnectionCheckTimer($board)
    {
        $this->log("Checking for timeout [$board->id] board...");

        if (!isset($this->board_clients[$board->id])) {
            $this->logBoardConnection($board, false);

            return $this->log("Board [$board->id] has already been disconnected!");
        }

        if (isset($this->awaitingPong[$board->id])) {
            $this->log("There was no pong from last ping! Disconnecting...");

            return $this->board_clients[$board->id]->close();
        }

        $this->awaitingPong[$board->id] = true;

        $this->sendToBoard($board->id, [
            'type' => 'ping',
        ]);

        $this->isConnectedTimers[$board->id]->cancel();

        $this->isConnectedTimers[$board->id] = $this->loop->addTimer(
            Yii::$app->params['server']['connectionCheckTimeout'],
            function () use ($board) {
                return $this->doConnectionCheckTimer($board);
            }
        );

        return true;
    }

    /**
     * @param Item $item
     * @param string $value
     */
    private function trigItemValueEvent($item, $value)
    {
        // Find event
        $event = Event::findOne([
            'trig_item_id' => $item->id,
            'trig_item_value' => $value,
        ]);

        if (!$event) {
            return;
        }

        $this->trigEvent($event);
    }

    /**
     * @param TaskAction $action
     */
    private function doTaskAction($action)
    {
        switch ($action->type) {
            case TaskAction::TYPE_CHANGE_ITEM_VALUE:
                $item = $action->item;

                switch ($item->type) {
                    case Item::TYPE_SWITCH:
                        $data = [
                            'type' => $action->item_value == '1' ? 'turnON' : 'turnOFF',
                            'pin' => $item->pin,
                        ];

                        break;
                    case Item::TYPE_RGB:
                        $rgbData = $this->valueToRgbData($action->item_value);
                        $red = $rgbData[0];
                        $green = $rgbData[1];
                        $blue = $rgbData[2];
                        $fade = (bool)$rgbData[3];

                        $data = [
                            'type' => 'rgb',
                            'red' => $red * 4,
                            'green' => $green * 4,
                            'blue' => $blue * 4,
                            'fade' => $fade,
                        ];

                        break;
                    default:
                        $data = [
                            'type' => 'value',
                            'value' => $action->item_value,
                            'pin' => $item->pin,
                        ];

                        break;
                }

                $this->sendToBoard($item->board_id, $data);

                break;
        }
    }

    private function valueToRgbData($value)
    {
        return explode(',', $value);
    }

    /**
     * @param Event $event
     */
    private function trigEvent($event)
    {
        $this->log("Event [{$event->id}] triggered. Doing the tasks...");

        // Do the tasks
        $task = $event->task;

        foreach ($task->taskActions as $action) {
            $this->doTaskAction($action);
        }

        $this->log("Tasks for Event [{$event->id}] done!");
    }

    /**
     * @param Event $event
     */
    private function trigDateEvent($event)
    {
        $this->trigEvent($event);
    }

    /**
     * @param Event $event
     */
    private function trigTimeEvent($event)
    {
        $this->trigEvent($event);

        $this->scheduleEvents();
    }

    protected function scheduleEvents()
    {
        $this->log("Scheduling Events...");

        /** @var Event[] $events */
        $events = Event::find()->where([
            'type' => [
                Event::TYPE_BY_DATE,
                Event::TYPE_BY_TIME,
            ],
        ])->all();

        foreach ($events as $event) {
            switch ($event->type) {
                case Event::TYPE_BY_DATE:
                    if (isset($this->eventTimers[$event->id])) {
                        $this->log("Event [{$event->id}] already scheduled");
                        break;
                    }

                    if ($event->trig_date and $event->trig_date > time()) {
                        $timeout = $event->trig_date - time();

                        $this->log("Scheduling Event [{$event->id}] with timeout $timeout sec.");

                        $this->eventTimers[$event->id] = $this->loop->addTimer(
                            $timeout,
                            function () use ($event) {
                                $this->log("Event [{$event->id}] triggered by date");

                                if (isset($this->eventTimers[$event->id])) {
                                    unset($this->eventTimers[$event->id]);
                                }

                                return $this->trigDateEvent($event);
                            });
                    } else {
                        $this->log("Event [{$event->id}] expired by date");
                    }

                    break;
                case Event::TYPE_BY_TIME:
                    if ($event->trig_time_wdays != '') {  // Every week events
                        $this->log("Event [{$event->id}] runs every week");

                        $days = explode(',', $event->trig_time_wdays);

                        foreach ($days as $day) {
                            $trigTimestamp = strtotime($day . ', ' . $event->trig_time);

                            if (strtolower(date('w')) == $day) {
                                $trigTimestamp = strtotime('+1 week, ' . $event->trig_time);
                            }

                            if (isset($this->eventTimers[$event->id][$trigTimestamp])) {
                                $this->log("Event [{$event->id}] already scheduled by time [$trigTimestamp]");
                                break;
                            }

                            if (time() < $trigTimestamp) {
                                $timeout = $trigTimestamp - time();

                                $this->log("Scheduling Event [{$event->id}] with timeout $timeout sec. for timeout [$trigTimestamp]");

                                $this->eventTimers[$event->id][$trigTimestamp] = $this->loop->addTimer(
                                    $timeout,
                                    function () use ($event, $trigTimestamp) {
                                        $this->log("Event [{$event->id}] triggered by time [$trigTimestamp]");

                                        if (isset($this->eventTimers[$event->id][$trigTimestamp])) {
                                            unset($this->eventTimers[$event->id][$trigTimestamp]);
                                        }

                                        return $this->trigTimeEvent($event);
                                    }
                                );
                            } else {
                                $this->log("Trigger time $trigTimestamp is lower than current time " . time());
                            }
                        }
                    } else {    // Everyday events
                        $this->log("Event [{$event->id}] runs every day");

                        if (isset($this->eventTimers[$event->id])) {
                            $this->log("Event [{$event->id}] already scheduled by time");
                            break;
                        }

                        // Schedule event for today
                        $trigTimestamp = strtotime('today, ' . $event->trig_time);

                        if (time() < $trigTimestamp) {
                            $timeout = $trigTimestamp - time();

                            $this->log("Scheduling Event [{$event->id}] with timeout $timeout sec.");

                            $this->eventTimers[$event->id] = $this->loop->addTimer(
                                $timeout,
                                function () use ($event) {
                                    $this->log("Event [{$event->id}] triggered by time");

                                    if (isset($this->eventTimers[$event->id])) {
                                        unset($this->eventTimers[$event->id]);
                                    }

                                    return $this->trigTimeEvent($event);
                                }
                            );
                        } else {
                            $trigTimestamp = strtotime('tomorrow, ' . $event->trig_time);

                            $timeout = $trigTimestamp - time();

                            $this->log("Event [{$event->id}] expired by time. Scheduling for the next day ($timeout sec.)...");

                            $this->eventTimers[$event->id] = $this->loop->addTimer(
                                $timeout,
                                function () use ($event) {
                                    $this->log("Event [{$event->id}] triggered by time");

                                    if (isset($this->eventTimers[$event->id])) {
                                        unset($this->eventTimers[$event->id]);
                                    }

                                    return $this->trigTimeEvent($event);
                                }
                            );
                        }
                    }

                    break;
            }
        }

        $this->log("Scheduling done. Total count of timers: " . count($this->eventTimers));
    }
}
