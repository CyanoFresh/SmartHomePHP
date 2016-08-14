<?php

namespace app\servers;

use app\models\History;
use app\models\Item;
use app\models\User;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
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
                $this->loop->addPeriodicTimer($item->save_history_interval, function () {
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

        // Welcome user
        return $this->auth($conn, $query);
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
        $data = json_decode($msg, true);

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
        echo "An error has occurred: {$e->getMessage()} in file {$e->getFile()} at line {$e->getLine()}" . PHP_EOL;

        // Close connection
        $conn->close();
    }

    /**
     * Authenticate connected user
     *
     * @param ConnectionInterface $conn
     * @param $query
     * @return bool
     */
    protected function auth(ConnectionInterface $conn, $query)
    {
        $id = $query->get('id');
        $time = $query->get('time');
        $token = $query->get('token');

        if ((time() - $time) >= Yii::$app->params['auth']['tokenExpireSec']) {
            echo 'Token expired' . PHP_EOL;

            return false;
        }

        // Find user by auth info
        /** @var User $user */
        $user = User::findOne([
            'id' => $id,
            'auth_key' => $token,
        ]);

        // Security checks
        if (!$user) {
            echo 'Wrong token or id: steamID: "' . $id . '", token: "' . $token . '", IP: ' . $conn->remoteAddress . ', time: ' . $time . PHP_EOL;

            return false;
        }

        // Close previous connection
        if (isset($this->clients[$user->id])) {
            $this->clients[$user->id]->close();
        }

        // Attach to online users
        $conn->User = $user;
        $this->clients[$user->id] = $conn;

        return true;
    }

    /**
     * @param ConnectionInterface $from
     * @param User $user
     * @param array $data
     * @return bool|mixed
     */
    protected function handleWithdraw(ConnectionInterface $from, $user, $data)
    {
        $opening_id = (int)$data['opening_id'];
        $trade_url = (string)$data['trade_url'];

        $this->log("New withdraw request");
        $this->log("Opening ID: $opening_id");

        if (!$trade_url or $trade_url == '') {
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => [
                    'key' => 'server.errors.no-trade-url',
                ],
            ]));
        }

        // Check Opening
        $opening = DropsOpening::findOne($opening_id);

        if (!$opening) {
            return false;
        }

        if ($opening->status !== DropsOpening::STATUS_AVAILABLE) {
            return false;
        }

        if ($opening->steamid != $user->steamid) {
            return false;
        }

        // Check Item
        $roomItem = $opening->roomItem;

        if (!$roomItem) {
            return false;
        }

        // Parse Trade URL
        $tradeUrlQuery = parse_url($trade_url)['query'];
        parse_str($tradeUrlQuery, $tradeUrlData);

        $partnerID = $tradeUrlData['partner'];
        $tradeUrlToken = $tradeUrlData['token'];

        if (!$partnerID or $partnerID == '') {
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => [
                    'key' => 'server.errors.trade-url-not-valid',
                ],
            ]));
        }

        $this->log("Trade URL partnerID: $partnerID; Token: $tradeUrlToken;");

        if (!$tradeUrlToken or $tradeUrlToken == '') {
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => [
                    'key' => 'server.errors.trade-url-not-valid',
                ],
            ]));
        }

        if ($partnerID != Helper::toAccountID($user->steamid)) {
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => [
                    'key' => 'server.errors.trade-url-not-valid',
                ],
            ]));
        }

        // Create withdraw
        $itemWithdraw = new ItemWithdraw();
        $itemWithdraw->status = ItemWithdraw::STATUS_QUEUE;
        $itemWithdraw->steamid = $user->steamid;
        $itemWithdraw->opening_id = $opening->id;
        $itemWithdraw->room_item_id = $roomItem->id;
        $itemWithdraw->save();

        // Handle bot
        /** @var Bot $bot */
        $bot = $roomItem->bot;

        if (!$bot) {
            return false;
        }

        $this->log("Bot ID: {$bot->id}");

        $this->log("Connecting to the Steam...");
        $time = microtime(true);

        // Connect to the Steam
        $steam = $bot->processing()->getSteam();

        if (!$steam) {
            return false;
        }

        $this->log("Connected (" . round(microtime(true) - $time, 1) . "s)");

        $this->log("Fetching Offers...");
        $time = microtime(true);

        $tradeOffers = $steam->tradeOffers();

        $this->log("Fetched (" . round(microtime(true) - $time, 1) . "s)");

        // Create Trade
        $secureCode = $this->generateTradeCode();

        $trade = $tradeOffers->createTrade($partnerID);

        $trade->addMyItem(730, 2, $roomItem->assetid);
        $trade->setMessage("Secure code: $secureCode");

        $this->log("Sending trade...");
        $time = microtime(true);

        $tradeID = $trade->sendWithToken($tradeUrlToken);

        $this->log("Sent (" . round(microtime(true) - $time, 1) . "s)");

        $this->log("Result: $tradeID");

        if ($tradeID == 0) {
            $this->log('Cannot send trade');

            return $from->send(Json::encode([
                'type' => 'error',
                'message' => [
                    'key' => 'server.errors.cannot-create-trade',
                    'values' => [
                        'error' => $trade->getError(),
                    ],
                ],
            ]));
        }

        $this->log('Trade successfully sent. Confirming...');

        // Confirm on mobile
        $confirmMobileResult = false;

        while (!$confirmMobileResult) {
            $confirmMobileResult = $this->confirmTradeMobile($steam, $user, $tradeID);
        }

        // Save & send result
        $bot->notProcessing();

        $itemWithdraw->status = ItemWithdraw::STATUS_ACTIVE;
        $itemWithdraw->tradeid = $tradeID;
        $itemWithdraw->code = $secureCode;
        $itemWithdraw->save();

        return $from->send(Json::encode([
            'type' => 'trade_created',
            'tradeid' => $tradeID,
            'opening_id' => $opening->id,
            'code' => $secureCode,
        ]));
    }

    /**
     * Confirm mobile trade by tradeID and partner Username
     *
     * @param SteamCommunity $steam
     * @param User $user
     * @param string $tradeID
     * @return bool
     */
    private function confirmTradeMobile($steam, $user, $tradeID)
    {
        $this->log('Loading mobile confirmations...');

        // Load all available mobile confirmations in loop
        $confirmations = [];

        while ($confirmations == []) {
            try {
                $confirmations = $steam->mobileAuth()->confirmations()->fetchConfirmations();
            } catch (WgTokenInvalidException $ex) {
                $this->log('Regenerating mobile session...');
                $steam->mobileAuth()->refreshMobileSession();
                $this->log('Regenerated');
            }
        }

        $this->log('Loaded');

        $this->log('Looking for confirmation by username and trade ID...');

        // Find our confirmation
        foreach ($confirmations as $confirmation) {
            // Check for username
            if (stristr($confirmation->getConfirmationDescription(), $user->username) === false) {
                $this->log("Not suitable due to the username");
                continue;
            }

            // Get current trade id
            $confirmationTradeId = '0';

            $this->log("Getting confirmation TradeOffer ID...");

            while ($confirmationTradeId == '0') {
                $confirmationTradeId = $steam->mobileAuth()->confirmations()->getConfirmationTradeOfferId($confirmation);
            }

            // Check for trade id
            if ($confirmationTradeId != $tradeID) {
                $this->log("Not suitable due to the Trade ID");
                continue;
            }

            $this->log('Confirming trade...');

            // Confirm trade in loop
            $confirmResult = false;

            while (!$confirmResult) {
                $confirmResult = $steam->mobileAuth()->confirmations()->acceptConfirmation($confirmation);
            }

            $this->log('Confirmed');

            return true;
        }

        $this->log('Confirmation was not found');

        return false;
    }

    /**
     * @param ConnectionInterface $from
     * @param User $user
     * @param array $data
     * @return bool|mixed
     */
    protected function handleUpdate(ConnectionInterface $from, $user, $data)
    {
        $tradeID = (int)$data['tradeID'];

        if (!$tradeID) {
            return false;
        }

        // Check Opening
        $itemWithdraw = ItemWithdraw::findOne(['tradeid' => $tradeID]);

        if (!$itemWithdraw) {
            return false;
        }

        if ($itemWithdraw->status !== ItemWithdraw::STATUS_ACTIVE) {
            return false;
        }

        if ($itemWithdraw->steamid != $user->steamid) {
            return false;
        }

        $opening = $itemWithdraw->opening;
        $roomItem = $opening->roomItem;

        // Handle bot
        /** @var Bot $bot */
        $bot = $roomItem->bot;

        if (!$bot) {
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => [
                    'key' => 'server.errors.bot-not-available',
                ],
            ]));
        }

        $this->log("Connecting to the Steam...");
        $time = microtime(true);

        // Connect to the Steam
        $steam = $bot->processing()->getSteam();

        if (!$steam) {
            $bot->notProcessing();
            return $from->send(Json::encode([
                'type' => 'error',
                'message' => [
                    'key' => 'server.errors.bot-not-available',
                ],
            ]));
        }

        $this->log("Connected (" . round(microtime(true) - $time, 1) . "s)");

        $tradeOffers = $steam->tradeOffers();

        $this->log("Fetched (" . round(microtime(true) - $time, 1) . "s)");

        $this->log("Fetching Offer by ID...");
        $time = microtime(true);

        $offer = $tradeOffers->getTradeOfferViaAPI($tradeID);

        $this->log("Fetched (" . round(microtime(true) - $time, 1) . "s)");

        if (is_null($offer)) {
            $bot->notProcessing();

            return $from->send(Json::encode([
                'type' => 'close_active_trade',
            ]));
        }

        // Handle Trade Offer state
        $offerState = $offer->getTradeOfferState();

        switch ($offerState) {
            case State::Accepted:
                $itemWithdraw->status = ItemWithdraw::STATUS_ENDED;
                $itemWithdraw->save();

                $opening->status = DropsOpening::STATUS_WITHDRAWN;
                $opening->save();

                $bot->notProcessing();

                return $from->send(Json::encode([
                    'type' => 'close_active_trade',
                ]));

                break;

            case State::Active:
                return false;

            case State::Invalid:
            case State::Expired:
            case State::Canceled:
            case State::Declined:
            case State::InvalidItems:
                $itemWithdraw->status = ItemWithdraw::STATUS_CANCELED;
                $itemWithdraw->save();

                $bot->notProcessing();

                return $from->send(Json::encode([
                    'type' => 'canceled_active_trade',
                    'opening_id' => $opening->id,
                ]));

                break;

            case State::Countered:
            case State::CanceledBySecondFactor:
            case State::InEscrow:
                // Cancel because we don't want to trade in this cases
                $tradeOffers->declineTradeById($tradeID);

                $itemWithdraw->status = ItemWithdraw::STATUS_CANCELED;
                $itemWithdraw->save();

                $bot->notProcessing();

                return $from->send(Json::encode([
                    'type' => 'canceled_active_trade',
                    'opening_id' => $opening->id,
                ]));

                break;
            default:
                return false;
        }
    }

    /**
     * Send data to all clients
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
     * Send data to specific client with given SteamID
     *
     * @param string|integer $steamID
     * @param array $data
     * @return bool
     */
    private function sendTo($steamID, $data)
    {
        if (isset($this->clients[$steamID])) {
            /** @var ConnectionInterface $client */
            $client = $this->clients[$steamID];

            $client->send(Json::encode($data));

            return true;
        }

        return false;
    }

    /**
     * Generate code for trade message
     *
     * @return string
     */
    private function generateTradeCode()
    {
        return strtoupper(Yii::$app->security->generateRandomString(5));
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
                $value = $this->getValue($item);
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

    /**
     * @param Item $item
     */
    private function getValue($item)
    {
    }
}
