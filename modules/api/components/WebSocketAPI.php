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
    public $localWSSUrl;

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
        return $this->localWSSUrl . '/?type=user&id=' . $this->user->id . '&auth_key=' . $this->user->auth_key;
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
     * @param array $data
     * @return bool
     */
    protected function send($data)
    {
        $loop = Factory::create();
        $connector = new Connector($loop);

        $afterConnect = function (WebSocket $conn) use ($data) {
            // Send data
            $conn->send(Json::encode($data));

            // Job done. Close the connection
            $conn->close();

            return true;
        };

        $afterDisconnect = function(\Exception $e) use ($loop) {
            echo "Could not connect: {$e->getMessage()}\n";
            $loop->stop();
        };

        $connector($this->getWSSUrl(), [], ['Origin' => 'origin'])
            ->then($afterConnect, $afterDisconnect);

        $loop->run();

        return ($afterConnect == true and $afterDisconnect == false);
    }
}
