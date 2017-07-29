<?php

namespace app\modules\api\components;

use app\models\User;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use React\EventLoop\Factory;
use Yii;
use yii\helpers\Json;

class WebSocketAPIBridge
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $localWSSUrl;

    /**
     * WebSocketAPI constructor.
     * @param User $user
     * @param string|null $localWSSUrl
     */
    public function __construct($user, $localWSSUrl = null)
    {
        $this->user = $user;
        $this->localWSSUrl = $localWSSUrl;

        if (is_null($this->localWSSUrl)) {
            $this->localWSSUrl = Yii::$app->params['localWSURL'];
        }
    }

    /**
     * @return string
     */
    protected function getWSSUrl()
    {
        return $this->localWSSUrl . '/?type=user&id=' . $this->user->id . '&auth_token=' . $this->user->getAuthToken();
    }

    /**
     * @param integer $itemID
     * @return array
     */
    public function getValue($itemID)
    {
        $response = $this->sendAndReceive([]);

        if ($response['type'] == 'init' and count($response['items']) > 0) {
            foreach ($response['items'] as $item) {
                if ($item['id'] == $itemID and !is_null($item['value'])) {
                    return [
                        'success' => true,
                        'value' => $item['value'],
                        'item' => $item,
                    ];
                }
            }
        }

        return [
            'success' => false,
        ];
    }

    /**
     * @param int $itemID
     * @return bool
     */
    public function turnOn($itemID)
    {
        return $this->send([
            'type' => 'turn_on',
            'item_id' => $itemID,
        ]);
    }

    /**
     * @param int $itemID
     * @return bool
     */
    public function turnOff($itemID)
    {
        return $this->send([
            'type' => 'turn_off',
            'item_id' => $itemID,
        ]);
    }

    /**
     * @param int $triggerID
     * @return bool
     */
    public function trig($triggerID)
    {
        return $this->send([
            'type' => 'trig',
            'trigger_id' => $triggerID,
        ]);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function send($data)
    {
        $loop = Factory::create();
        $connector = new Connector($loop);

        $success = false;

        $connector($this->getWSSUrl(), [], ['Origin' => 'origin'])
            ->then(function (WebSocket $conn) use ($data, &$success) {
                // Send data
                $conn->send(Json::encode($data));

                $success = true;

                $conn->close();
            }, function (\Exception $e) use ($loop) {
                echo "Could not connect: {$e->getMessage()}\n";

                $loop->stop();
            });

        $loop->run();

        return $success;
    }

    /**
     * Send message and get response
     *
     * @param array $data Message to send
     * @return array|bool
     */
    public function sendAndReceive($data)
    {
        $loop = Factory::create();
        $connector = new Connector($loop);

        $result = false;

        $connector($this->getWSSUrl(), [], ['Origin' => 'origin'])
            ->then(function (WebSocket $conn) use ($data, &$result) {
                // Send data if not empty
                if (count($data) > 0) {
                    $conn->send(Json::encode($data));
                }

                $conn->on('message', function ($msg) use ($conn, &$result) {
                    $result = Json::decode($msg);
                    $conn->close();
                });
            }, function (\Exception $e) use ($loop) {
                echo "Could not connect: {$e->getMessage()}\n";

                $loop->stop();
            });

        $loop->run();

        return $result;
    }
}
