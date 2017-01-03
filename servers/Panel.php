<?php

namespace app\servers;

use app\helpers\IPHelper;
use app\models\Board;
use app\models\Setting;
use app\models\Task;
use app\models\Trigger;
use app\models\History;
use app\models\Item;
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
     * Format like this:
     *
     * `item_id => item_value`
     *
     * @var array
     */
    protected $item_values;

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
        $this->item_values = [];

        // Database driver hack: Prevent MySQL for disconnecting by timeout
        Yii::$app->db->createCommand('SET SESSION wait_timeout = 2147483;')->execute();
        $this->loop->addPeriodicTimer(8600, function () {
            Yii::$app->db->createCommand('SHOW TABLES;')->execute();
        });

        $this->updateItems();

        $this->scheduleTriggers();

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
                $userAuthToken = $query->get('auth_token');

                if (!$userID or !$userAuthToken) {
                    return $conn->close();
                }

                $user = User::findOne([
                    'id' => $userID,
                ]);

                if (!$user) {
                    $conn->close();
                    return $this->log("User was not found with id $userID");
                }

                if ($user->getAuthToken() != $userAuthToken) {
                    $conn->close();
                    return $this->log("User [$user->id] wrong auth token ($userAuthToken and {$user->getAuthToken()})");
                }

                // API request
                $api = false;

                if ($conn->remoteAddress == '127.0.0.1' and $conn->WebSocket->request->getHeader('Origin') == 'origin') {
                    $api = true;
                }

                // Close previous connection if it is not an API connection
                if (isset($this->user_clients[$user->id]) and !$api) {
                    $this->user_clients[$user->id]->close();
                }

                // Regenerate auth key
                $user->reGenerateAuthToken();

                // Attach to users
                $conn->User = $user;
                $conn->api = $api;
                $this->user_clients[$user->id] = $conn;

                // Prepare Items for User
                $items = Item::find()
                    ->active()
                    ->select(['id', 'type', 'room_id', 'board_id', 'default_value'])
                    ->asArray()
                    ->all();

                for ($i = 0; $i < count($items); $i++) {
                    $items[$i]['value'] = $this->getItemSavedValue($items[$i]['id'], $items[$i]['default_value']);
                }

                $conn->send(Json::encode([
                    'type' => 'init',
                    'items' => $items,
                ]));

                $this->logUserConnection($user, true);

                return $this->log("Connected user [{$user->id}] {$user->username}");
            case 'board':
                $this->log('Connection type is Board. Authenticating...');

                $boardID = $query->get('id');
                $boardSecret = $query->get('secret');

                if (!$boardID or !$boardSecret) {
                    $this->log('Wrong login data');
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

                if (!IPHelper::isLocal($conn->remoteAddress) and !$board->remote_connection) {
                    $this->log("Remote connection blocked for board [$board->id]; IP: {$conn->remoteAddress}");
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

                // Set default values to board's items
                foreach ($board->items as $item) {
                    if ($item->default_value and !is_null($item->default_value)) {
                        switch ($item->type) {
                            case Item::TYPE_SWITCH:
                                $this->sendToBoard($board->id, [
                                    'type' => $item->default_value == 1 ? 'turnON' : 'turnOFF',
                                    'pin' => $item->pin,
                                ]);

                                break;
                            case Item::TYPE_RGB:
                                $rgbData = $this->valueToRgbData($item->default_value);

                                $red = $rgbData[0];
                                $green = $rgbData[1];
                                $blue = $rgbData[2];
                                $fade = (bool)$rgbData[3];

                                $this->sendToBoard($board->id, [
                                    'type' => 'rgb',
                                    'red' => $red * 4,
                                    'green' => $green * 4,
                                    'blue' => $blue * 4,
                                    'fade' => $fade,
                                ]);

                                break;
                        }
                    }
                }

                $this->triggerBoardConnection($board, true);

                $this->logBoardConnection($board, true);

                return $this->log("Connected board [{$board->id}]");
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

            $this->triggerBoardConnection($conn->Board, false);

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

        if (!isset($data['type']) or $data['type'] == '') {
            return $this->log("Unknown command from user: $msg");
        }

        switch ($data['type']) {
            case 'turnON':
                return $this->handleTurnOn($from, $user, $data);
            case 'turnOFF':
                return $this->handleTurnOff($from, $user, $data);
            case 'rgb':
                return $this->handleRgb($from, $user, $data);
            case 'rgbMode':
                return $this->handleRgbMode($from, $user, $data);
            case 'schedule-triggers':
                return $this->scheduleTriggers();
            case 'update-items':
                return $this->updateItems();
            case 'trig':
                return $this->handleTrig($from, $user, $data);
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
                $this->triggerItemValue($item, $value);

                $value = $this->saveItemValue($item->id, $value, $item->type);

                $this->sendUsers([
                    'type' => 'value',
                    'item_id' => $item->id,
                    'item_type' => $item->type,
                    'value' => $value,
                ]);

                // Save to history
                $this->logItemValue($item, $value);

                break;
            case 'rgbMode':
                $value = $data['mode'];
                $pin = (integer)$data['pin'];
                $start = (bool)$data['start'];

                $item = Item::findOne([
                    'board_id' => $board->id,
                    'pin' => $pin,
                ]);

                if (!$item) {
                    return $this->log('Trying to use unknown item');
                }

                // Trig event
                // TODO
//                $this->triggerItemValue($item, $value);

                if ($start) {
                    $this->saveItemValue($item->id, $value, $item->type);
                } else {
                    $this->saveItemValue($item->id, $item->getDefaultValue(), $item->type);
                }

                $this->sendUsers([
                    'type' => 'value',
                    'item_id' => $item->id,
                    'item_type' => $item->type,
                    'value' => $value,
                    'start' => $start,
                ]);

                // Save to history
                // TODO
//                $this->logItemValue($item, $value);

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
                    $this->triggerItemValue($item, $value);

                    $value = $this->saveItemValue($item->id, $value, $item->type);

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
                $this->log("Pong from board [$board->id]");
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

//        $rgbArray = [
//            $red,
//            $green,
//            $blue
//        ];

//        $this->item_values[$item->id]['value'] = $rgbArray;

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
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => 'Такое устройство не существует',
            ]));
        }

        if ($item->type !== Item::TYPE_RGB) {
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => 'Данный тип устройства не является RGB',
            ]));
        }

        $mode = $data['mode'];
        $start = (bool)$data['start'];

        if (!in_array($mode, Item::getModesArray())) {
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => 'Неизвестный режим',
            ]));
        }

        $board = $item->board;

        switch ($board->type) {
            case Board::TYPE_AREST:
                throw new NotSupportedException();

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
                    'action' => $start ? 'start' : 'stop',
                ]);

                break;
        }

        $history = new History();
        $history->type = History::TYPE_USER_ACTION;
        $history->user_id = $user->id;
        $history->item_id = $item->id;
        $history->commited_at = time();
        $history->value = $mode . ', ' . $start ? 'start' : 'stop';

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
    private function handleTrig($from, $user, $data)
    {
        $trigger_id = (int)$data['trigger_id'];
        $trigger = Trigger::findOne($trigger_id);

        if (!$trigger) {
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => 'Такое триггер не существует',
            ]));
        }

        if ($trigger->type !== Trigger::TYPE_MANUAL) {
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => 'Данный тип устройства не является Manual',
            ]));
        }

        $this->log("Trigger [$trigger->id] triggered by manual");

        $this->trigger($trigger);

        $history = new History();
        $history->type = History::TYPE_API_TRIGGER;
        $history->user_id = $user->id;
        $history->commited_at = time();

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
        if (!Setting::getValueByKey('log.board_connection')) {
            return;
        }

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
        if (!Setting::getValueByKey('log.user_connection')) {
            return;
        }

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
        if (!$item->enable_log) {
            $this->log("Logging for this item is disabled");
            return;
        }

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
    private function triggerItemValue($item, $value)
    {
        // Find Triggers
        /** @var Trigger[] $triggers */
        $triggers = Trigger::findAll([
            'active' => true,
            'trig_item_id' => $item->id,
            'trig_item_value' => $value,
        ]);

        if (!$triggers) {
            return;
        }

        foreach ($triggers as $trigger) {
            $this->trigger($trigger);
        }
    }

    /**
     * @param Board $board
     * @param bool $connected
     */
    private function triggerBoardConnection(Board $board, bool $connected)
    {
        /** @var Trigger[] $triggers */
        $triggers = Trigger::findAll([
            'active' => true,
            'trig_board_id' => $board->id,
            'trig_connection_value' => $connected ? Trigger::CONNECTION_VALUE_CONNECTED : Trigger::CONNECTION_VALUE_DISCONNECTED,
        ]);

        if (!$triggers) {
            return;
        }

        foreach ($triggers as $trigger) {
            $this->trigger($trigger);
        }
    }

    /**
     * @param Task $task
     */
    private function doTask($task)
    {
        switch ($task->type) {
            case Task::TYPE_ITEM_VALUE:
                $item = $task->item;

                switch ($item->type) {
                    case Item::TYPE_SWITCH:
                        $data = [
                            'type' => $task->item_value == '1' ? 'turnON' : 'turnOFF',
                            'pin' => $item->pin,
                        ];

                        break;
                    case Item::TYPE_RGB:
                        $rgbData = $this->valueToRgbData($task->item_value);

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
                            'value' => $task->item_value,
                            'pin' => $item->pin,
                        ];

                        break;
                }

                $this->sendToBoard($item->board_id, $data);

                break;
            case Task::TYPE_NOTIFICATION_TELEGRAM:
                $result = $task->sendNotificationTelegram();

                if (!$result) {
                    $this->log("Cannot send telegram message");
                } else {
                    $this->log("Message sent");
                }

                break;
            default:
                $this->log("Unknown task type: {$task->type}");
                break;
        }
    }

    /**
     * @param string $value
     * @return array
     */
    private function valueToRgbData($value)
    {
        return explode(',', $value);
    }

    /**
     * @param string $value
     * @return array
     */
    private function valueToRgb($value)
    {
        if (!is_array($value)) {
            $value = $this->valueToRgbData($value);
        }

        // Convert R,G,B from 10 bit to 8 bit
        for ($i = 0; $i < 3; $i++) {
            $value[$i] = round($value[$i] / 4);
        }

        return $value;
    }

    /**
     * @param Trigger $trigger
     */
    private function triggerDate($trigger)
    {
        $this->trigger($trigger);
    }

    /**
     * @param Trigger $trigger
     */
    private function triggerTime($trigger)
    {
        $this->trigger($trigger);

        $this->scheduleTriggers();
    }

    /**
     * @param Trigger $trigger
     */
    private function trigger($trigger)
    {
        $this->log("Trigger [{$trigger->id}] triggered. Doing the tasks...");

        // Do the tasks
        foreach ($trigger->tasks as $task) {
            $this->doTask($task);
        }

        $this->log("Tasks for Trigger [{$trigger->id}] done!");
    }

    /**
     * Schedule Trigger's triggering by time or date
     */
    protected function scheduleTriggers()
    {
        $this->log("Scheduling Triggers...");

        /** @var Trigger[] $triggers */
        $triggers = Trigger::find()->where([
            'active' => true,
            'type' => [
                Trigger::TYPE_BY_DATE,
                Trigger::TYPE_BY_TIME,
            ],
        ])->all();

        foreach ($triggers as $trigger) {
            switch ($trigger->type) {
                case Trigger::TYPE_BY_DATE:
                    if (isset($this->eventTimers[$trigger->id])) {
                        $this->log("Trigger [{$trigger->id}] already scheduled");
                        break;
                    }

                    if ($trigger->trig_date and $trigger->trig_date > time()) {
                        $timeout = $trigger->trig_date - time();

                        $this->log("Scheduling Trigger [{$trigger->id}] with timeout $timeout sec.");

                        $this->eventTimers[$trigger->id] = $this->loop->addTimer(
                            $timeout,
                            function () use ($trigger) {
                                $this->log("Trigger [{$trigger->id}] triggered by date");

                                if (isset($this->eventTimers[$trigger->id])) {
                                    unset($this->eventTimers[$trigger->id]);
                                }

                                return $this->triggerDate($trigger);
                            });
                    } else {
                        $this->log("Trigger [{$trigger->id}] expired by date");
                    }

                    break;
                case Trigger::TYPE_BY_TIME:
                    if ($trigger->trig_time_wdays != '') {  // Every week triggers
                        $this->log("Trigger [{$trigger->id}] runs every week");

                        $days = explode(',', $trigger->trig_time_wdays);

                        foreach ($days as $day) {
                            $trigTimestamp = strtotime($day . ', ' . $trigger->trig_time);

                            if (strtolower(date('l')) == $day) {
                                $trigTimestamp = strtotime('+1 week, ' . $trigger->trig_time);
                            }

                            if (isset($this->eventTimers[$trigger->id][$trigTimestamp])) {
                                $this->log("Trigger [{$trigger->id}] already scheduled by time [$trigTimestamp]");
                                break;
                            }

                            if (time() < $trigTimestamp) {
                                $timeout = $trigTimestamp - time();

                                $this->log("Scheduling Trigger [{$trigger->id}] with timeout $timeout sec. for timeout [$trigTimestamp]");

                                $this->eventTimers[$trigger->id][$trigTimestamp] = $this->loop->addTimer(
                                    $timeout,
                                    function () use ($trigger, $trigTimestamp) {
                                        $this->log("Trigger [{$trigger->id}] triggered by time [$trigTimestamp]");

                                        if (isset($this->eventTimers[$trigger->id][$trigTimestamp])) {
                                            unset($this->eventTimers[$trigger->id][$trigTimestamp]);
                                        }

                                        return $this->triggerTime($trigger);
                                    }
                                );
                            } else {
                                $this->log("Trigger time $trigTimestamp is lower than current time " . time());
                            }
                        }
                    } else {    // Everyday triggers
                        $this->log("Trigger [{$trigger->id}] runs every day");

                        if (isset($this->eventTimers[$trigger->id])) {
                            $this->log("Trigger [{$trigger->id}] already scheduled by time");
                            break;
                        }

                        // Schedule trigger for today
                        $trigTimestamp = strtotime('today, ' . $trigger->trig_time);

                        if (time() < $trigTimestamp) {
                            $timeout = $trigTimestamp - time();

                            $this->log("Scheduling Trigger [{$trigger->id}] with timeout $timeout sec.");

                            $this->eventTimers[$trigger->id] = $this->loop->addTimer(
                                $timeout,
                                function () use ($trigger) {
                                    $this->log("Trigger [{$trigger->id}] triggered by time");

                                    if (isset($this->eventTimers[$trigger->id])) {
                                        unset($this->eventTimers[$trigger->id]);
                                    }

                                    return $this->triggerTime($trigger);
                                }
                            );
                        } else {
                            $trigTimestamp = strtotime('tomorrow, ' . $trigger->trig_time);

                            $timeout = $trigTimestamp - time();

                            $this->log("Trigger [{$trigger->id}] expired by time. Scheduling for the next day ($timeout sec.)...");

                            $this->eventTimers[$trigger->id] = $this->loop->addTimer(
                                $timeout,
                                function () use ($trigger) {
                                    $this->log("Trigger [{$trigger->id}] triggered by time");

                                    if (isset($this->eventTimers[$trigger->id])) {
                                        unset($this->eventTimers[$trigger->id]);
                                    }

                                    return $this->triggerTime($trigger);
                                }
                            );
                        }
                    }

                    break;
            }
        }

        $this->log("Done. Total count of timers: " . count($this->eventTimers));
    }

    /**
     * Fill with default value item values array
     */
    protected function updateItems()
    {
        $this->log("Loading items...");

        /** @var Item[] $items */
        $items = Item::find()->active()->all();

        foreach ($items as $item) {
            if (!$this->hasItemSavedValue($item->id)) {
                $this->saveItemValue($item->id, $item->getDefaultValue(), $item->type, false);
            }
        }

        $this->log("Done");
    }

    /**
     * @param int $item_id
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function getItemSavedValue($item_id, $defaultValue = false)
    {
        if ($this->hasItemSavedValue($item_id)) {
            return $this->item_values[$item_id];
        }

        return $defaultValue;
    }

    /**
     * @param int $item_id
     * @return bool
     */
    protected function hasItemSavedValue($item_id)
    {
        return isset($this->item_values[$item_id]);
    }

    /**
     * Saves to value array and returns it. Normalization is on by default
     *
     * @param int $item_id
     * @param mixed $value
     * @param int $item_type
     * @param bool $normalize
     * @return array|bool|int
     */
    protected function saveItemValue($item_id, $value, $item_type, $normalize = true)
    {
        if ($normalize) {
            $value = $this->normalizeItemValue($value, $item_type);
        }

        $this->item_values[$item_id] = $value;

        return $value;
    }

    /**
     * @param mixed $value
     * @param int $type
     * @return array|bool|int
     */
    protected function normalizeItemValue($value, $type)
    {
        switch ($type) {
            case Item::TYPE_SWITCH:
            case Item::TYPE_VARIABLE_BOOLEAN:
            case Item::TYPE_VARIABLE_BOOLEAN_DOOR:
                return $this->valueToBoolean($value);

            case Item::TYPE_VARIABLE_TEMPERATURE:
            case Item::TYPE_VARIABLE_HUMIDITY:
                return (int)$value;

            case Item::TYPE_RGB:
                return $this->valueToRgb($value);
        }

        return $value;
    }
}
