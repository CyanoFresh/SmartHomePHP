<?php

namespace app\modules\api\components;

use app\models\User;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use React\EventLoop\Factory;
use Yii;
use yii\helpers\Json;

class WebSocketAPI
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
     * @param int $itemID
     * @return bool
     */
    public function turnOn($itemID)
    {
        return $this->send([
            'type' => 'turnON',
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
            'type' => 'turnOFF',
            'item_id' => $itemID,
        ]);
    }

    /**
     * @param int $itemID
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param boolean $fade
     * @return bool
     * @internal param array $rgbData
     */
    public function rgb($itemID, $red, $green, $blue, $fade)
    {
        return $this->send([
            'type' => 'rgb',
            'item_id' => $itemID,
            'red' => $red,
            'green' => $green,
            'blue' => $blue,
            'fade' => $fade,
        ]);
    }

    /**
     * @param int $itemID
     * @param string $mode
     * @param bool $start
     * @return bool
     * @internal param array $rgbData
     */
    public function rgbMode($itemID, $mode, $start)
    {
        return $this->send([
            'type' => 'rgbMode',
            'item_id' => $itemID,
            'mode' => $mode,
            'start' => $start,
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

                // Job done. Close the connection
                $conn->close();

                $success = true;
            }, function(\Exception $e) use ($loop) {
                echo "Could not connect: {$e->getMessage()}\n";

                $loop->stop();
            });

        $loop->run();

        return $success;
    }
}
