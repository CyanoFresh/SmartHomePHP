<?php

namespace app\servers;

use app\helpers\IPHelper;
use app\models\Board;
use app\models\Event;
use app\models\Setting;
use app\models\Task;
use app\models\Trigger;
use app\models\History;
use app\models\Item;
use app\models\User;
use MongoDB\Driver\Exception\AuthenticationException;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use Yii;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * Class CoreServer
 *
 * Smart Home Websocket core handler
 */
class CoreServer implements MessageComponentInterface
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
     * `item_id` => TimerInterface
     * @var TimerInterface[]
     */
    protected $connectionCheckTimers;

    /**
     * `item_id` => iteration count
     * @var array
     */
    protected $connectionCheckIteration;

    /**
     * @var TimerInterface[]
     */
    protected $triggerTimers;

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
        $this->connectionCheckTimers = [];
        $this->connectionCheckIteration = [];
        $this->triggerTimers = [];

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
                    $this->log("Invalid credentials: '$userID', '$userAuthToken'");

                    throw new InvalidParamException("Required fields does not exist");
                }

                $user = User::findOne([
                    'id' => $userID,
                ]);

                if (!$user) {
                    $this->log("User was not found with id '$userID'");

                    throw new NotFoundHttpException("User was not found with id '$userID'");
                }

                if ($user->getAuthToken() != $userAuthToken) {
                    $this->log("User [$user->id] wrong auth token ($userAuthToken and {$user->getAuthToken()})");

                    throw new AuthenticationException("Wrong credentials");
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
                    $this->log("Wrong login data: '$boardID' and '$boardSecret'");

                    throw new UnauthorizedHttpException('Wrong credentials');
                }

                $board = Board::findOne([
                    'id' => $boardID,
                    'type' => Board::TYPE_WEBSOCKET,
                    'secret' => $boardSecret,
                ]);

                if (!$board) {
                    $this->log("Board [$boardID] not found!");

                    throw new NotFoundHttpException("Board with given ID does not exists");
                }

                if (!$board->remote_connection and !IPHelper::isLocal($conn->remoteAddress)) {
                    $this->log("Remote connection blocked for board [$boardID]; IP: {$conn->remoteAddress}");

                    throw new ForbiddenHttpException("Remote connection is not allowed for this Board");
                }

                // Attach to boards
                $conn->Board = $board;
                $this->board_clients[$board->id] = $conn;

                // Reset previous timer and start a new one
                $this->startConnectionCheckTimer($boardID, true);

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

                $this->logBoardConnection($board->id, true);

                return $this->log("Connected board [{$board->id}]");
        }

        return $this->log('Connection has unknown type. Disconnect');
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if (isset($from->User)) {
            $this->log("Message '$msg' from User [{$from->User->id}]");

            return $this->handleUserMessage($from, $msg);
        } elseif (isset($from->Board)) {
            $this->log("Message '$msg' from Board [{$from->Board->id}]");

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

            // Cancel connection check timer
            $this->stopConnectionCheckTimer($boardId);

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

        // Board responds: restart connection check timer
        $this->startConnectionCheckTimer($board->id);

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
                /**
                 * Message structure:
                 * {"start":true,"type":"rgbMode","mode":"rainbow","pin":1}
                 */
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

                if ($start) {
                    $value = $this->saveItemValue($item->id, $value, $item->type, false);
                } else {
                    $value = $this->saveItemValue($item->id, $item->getDefaultValue(), $item->type);
                }

                // TODO: trigger

                $this->sendUsers([
                    'type' => 'value',
                    'item_id' => $item->id,
                    'item_type' => $item->type,
                    'value' => $value,
                    'start' => $start,
                ]);

                // Save to history
                $this->logItemValue($item, $start ? 'start:' : '' . $value);

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
    protected function handleRgb($from, $user, $data)
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
    protected function handleRgbMode($from, $user, $data)
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
    protected function handleTrig($from, $user, $data)
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
    protected function sendUsers($data)
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
    protected function sendToBoard($board_id, $data)
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
     * Logs message into console or other storage
     *
     * @param string $message
     * @param bool $appendDate
     * @param bool $eol Put EndOfLine symbol at the end
     */
    protected function log($message, $appendDate = true, $eol = true)
    {
        $output = '';

        if ($appendDate) {
            $output .= '[' . Yii::$app->formatter->asDatetime(time(), 'yyyy-MM-dd HH:mm:ss') . '] ';
        }

        $output .= $message;

        if ($eol) {
            $output .= PHP_EOL;
        }

        echo $output;
    }

    /**
     * @param integer $boardID
     * @return bool
     */
    protected function isBoardConnected($boardID)
    {
        return isset($this->board_clients[$boardID]);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function valueToBoolean($value)
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
     * @param int $boardID
     * @param boolean $connected
     */
    protected function logBoardConnection($boardID, $connected)
    {
        if (!Setting::getValueByKey('log.board_connection')) {
            return;
        }

        $model = new History();
        $model->type = History::TYPE_BOARD_CONNECTION;
        $model->board_id = $boardID;
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

    /**
     * Do the Connection Check Timer job
     *
     * @param int $boardID
     */
    public function doConnectionCheckTimer($boardID)
    {
        $this->log("Connection check for Board [$boardID]...");

        // Check if it is already disconnected
        if (!isset($this->board_clients[$boardID])) {
            $this->logBoardConnection($boardID, false);

            $this->log("Board [$boardID] has already been disconnected");

            return;
        }

        if (isset($this->connectionCheckIteration[$boardID])) {
            if ($this->connectionCheckIteration[$boardID] >= Yii::$app->params['server']['connectionCheckMaxIteration']) {
                $this->log("Maximum ignored ping commands reached. Disconnecting...");

                $this->board_clients[$boardID]->close();

                return;
            }

            $this->connectionCheckIteration[$boardID]++;
        } else {
            $this->connectionCheckIteration[$boardID] = 1;
        }

        $this->sendToBoard($boardID, [
            'type' => 'ping',
        ]);
    }

    /**
     * @param Item $item
     * @param string $value
     */
    protected function triggerItemValue($item, $value)
    {
        // Find Triggers
        /** @var Trigger[] $triggers */
        $triggers = Trigger::findAll([
            'active' => true,
            'item_id' => $item->id,
            'item_value' => $value,
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
    protected function triggerBoardConnection(Board $board, bool $connected)
    {
        /** @var Trigger[] $triggers */
        $triggers = Trigger::findAll([
            'active' => true,
            'board_id' => $board->id,
            'connection_value' => $connected ? Trigger::CONNECTION_VALUE_CONNECTED : Trigger::CONNECTION_VALUE_DISCONNECTED,
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
    protected function doTask($task)
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
    protected function valueToRgbData($value)
    {
        return explode(',', $value);
    }

    /**
     * @param string $value
     * @return array
     */
    protected function valueToRgb($value)
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
    protected function triggerDate($trigger)
    {
        $this->trigger($trigger);
    }

    /**
     * @param Trigger $trigger
     */
    protected function triggerTime($trigger)
    {
        $this->trigger($trigger);

        $this->scheduleTriggers();
    }

    /**
     * Trigger it's events
     *
     * @param Trigger $trigger
     */
    protected function trigger($trigger)
    {
        /** @var Event[] $events */
        $events = $trigger->getEvents()->andWhere(['active' => true])->all();

        foreach ($events as $event) {
            $this->triggerEvent($event);
        }
    }

    /**
     * Do the Event's tasks
     *
     * @param Event $event
     */
    protected function triggerEvent(Event $event)
    {
        $this->log("Triggered Event [{$event->id}]");

        /** @var Task[] $tasks */
        $tasks = $event->getTasks()->andWhere(['active' => true])->all();

        foreach ($tasks as $task) {
            $this->doTask($task);
        }

        $event->last_triggered_at = time();
        $event->save(false);

        $this->log("Tasks for Event [{$event->id}] done");
    }

    /**
     * Schedule Trigger's by time or date
     */
    protected function scheduleTriggers()
    {
        $this->log("Scheduling DateTime Triggers...");

        /** @var Trigger[] $triggers */
        $triggers = Trigger::find()->where([
            'active' => true,
            'type' => [
                Trigger::TYPE_BY_DATE,
                Trigger::TYPE_BY_TIME,
            ],
        ])->all();

        foreach ($triggers as $trigger) {
            if ($trigger->type === Trigger::TYPE_BY_DATE) {
                // Check if it was already scheduled
                if (isset($this->triggerTimers[$trigger->id])) {
                    $this->log("Date Trigger [{$trigger->id}] was already been scheduled");
                    continue;
                }

                // Check if it's time is future
                if ($trigger->date and $trigger->date > time()) {
                    $timeout = $trigger->date - time();

                    $this->log("Scheduling Date Trigger [{$trigger->id}] with timeout $timeout sec.");

                    $this->triggerTimers[$trigger->id] = $this->loop->addTimer(
                        $timeout,
                        function () use ($trigger) {
                            $this->log("Trigger [{$trigger->id}] triggered by date");

                            if (isset($this->triggerTimers[$trigger->id])) {
                                unset($this->triggerTimers[$trigger->id]);
                            }

                            $this->triggerDate($trigger);
                        });

                    continue;
                }

                $this->log("Date Trigger [{$trigger->id}] has past date. Disabling it...");

                $trigger->active = false;
                $trigger->save();

                continue;
            } elseif ($trigger->type === Trigger::TYPE_BY_TIME) {
                // Check if it also triggers by weekdays
                if ($trigger->weekdays) {
                    $this->log("Time Trigger [{$trigger->id}] runs every week");

                    $days = explode(',', $trigger->weekdays);

                    foreach ($days as $day) {
                        $trigTimestamp = strtotime($day . ', ' . $trigger->time);

                        if (strtolower(date('l')) == $day) {
                            $trigTimestamp = strtotime('+1 week, ' . $trigger->time);
                        }

                        if (isset($this->triggerTimers[$trigger->id][$trigTimestamp])) {
                            $this->log("Time Trigger [{$trigger->id}] already scheduled by time [$trigTimestamp]");
                            continue;
                        }

                        if (time() < $trigTimestamp) {
                            $timeout = $trigTimestamp - time();

                            $this->log("Scheduling Time Trigger [{$trigger->id}] with timeout $timeout sec. for timeout [$trigTimestamp]");

                            $this->triggerTimers[$trigger->id][$trigTimestamp] = $this->loop->addTimer(
                                $timeout,
                                function () use ($trigger, $trigTimestamp) {
                                    $this->log("Trigger [{$trigger->id}] triggered by time [$trigTimestamp]");

                                    if (isset($this->triggerTimers[$trigger->id][$trigTimestamp])) {
                                        unset($this->triggerTimers[$trigger->id][$trigTimestamp]);
                                    }

                                    return $this->triggerTime($trigger);
                                }
                            );
                        } else {
                            $this->log("Trigger time $trigTimestamp is lower than current time " . time());
                        }
                    }
                } else {    // Everyday triggers
                    $this->log("Time Trigger [{$trigger->id}] runs every day");

                    if (isset($this->triggerTimers[$trigger->id])) {
                        $this->log("Time Trigger [{$trigger->id}] already scheduled by time");
                        continue;
                    }

                    // Schedule trigger for today
                    $trigTimestamp = strtotime('today, ' . $trigger->time);

                    if (time() < $trigTimestamp) {
                        $timeout = $trigTimestamp - time();

                        $this->log("Scheduling Time Trigger [{$trigger->id}] with timeout $timeout sec.");

                        $this->triggerTimers[$trigger->id] = $this->loop->addTimer(
                            $timeout,
                            function () use ($trigger) {
                                $this->log("Time Trigger [{$trigger->id}] triggered by time");

                                if (isset($this->triggerTimers[$trigger->id])) {
                                    unset($this->triggerTimers[$trigger->id]);
                                }

                                return $this->triggerTime($trigger);
                            }
                        );
                    } else {
                        $trigTimestamp = strtotime('tomorrow, ' . $trigger->time);

                        $timeout = $trigTimestamp - time();

                        $this->log("Time Trigger [{$trigger->id}] expired by time. Scheduling for the next day ($timeout sec.)...");

                        $this->triggerTimers[$trigger->id] = $this->loop->addTimer(
                            $timeout,
                            function () use ($trigger) {
                                $this->log("Time Trigger [{$trigger->id}] triggered by time");

                                if (isset($this->triggerTimers[$trigger->id])) {
                                    unset($this->triggerTimers[$trigger->id]);
                                }

                                return $this->triggerTime($trigger);
                            }
                        );
                    }
                }
            }
        }

        $this->log("Done. Total count of timers: " . count($this->triggerTimers));
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

    /**
     * Start connection check periodic timer and save it.
     * If timer for this board exists - stop it.
     * Also resets connection check iteration count.
     *
     * @param int $boardID
     * @param bool $stopPrevious
     */
    protected function startConnectionCheckTimer($boardID, $stopPrevious = true)
    {
        if ($stopPrevious) {
            $this->stopConnectionCheckTimer($boardID);
        }

        // Start connection checks
        $this->connectionCheckTimers[$boardID] = $this->loop->addPeriodicTimer(
            Yii::$app->params['server']['connectionCheckTimeout'],
            function () use ($boardID) {
                $this->doConnectionCheckTimer($boardID);
            }
        );
    }

    /**
     * Stop connection check timer and reset connection checks count, if needed
     *
     * @param int $boardID
     * @param bool $resetCount
     */
    protected function stopConnectionCheckTimer($boardID, $resetCount = true)
    {
        if (isset($this->connectionCheckTimers[$boardID])) {
            if ($this->connectionCheckTimers[$boardID] instanceof TimerInterface) {
                $this->connectionCheckTimers[$boardID]->cancel();
            }

            unset($this->connectionCheckTimers[$boardID]);
        }

        if ($resetCount and isset($this->connectionCheckIteration[$boardID])) {
            unset($this->connectionCheckIteration[$boardID]);
        }
    }
}
