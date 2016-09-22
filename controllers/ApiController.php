<?php

namespace app\controllers;

use app\models\Item;
use app\models\User;
use Devristo\Phpws\Client\WebSocket;
use React\EventLoop\Factory;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class ApiController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionTurnOff($itemID, $userID, $apiKey)
    {
        return $this->switchItem($itemID, $userID, $apiKey, false);
    }

    public function actionTurnOn($itemID, $userID, $apiKey)
    {
        return $this->switchItem($itemID, $userID, $apiKey);
    }

    private function switchItem($itemId, $userId, $apiKey, $turnOn = true)
    {
        $user = User::findOne($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        if ($user->api_key != $apiKey) {
            throw new ForbiddenHttpException('Wrong API key');
        }

        $item = Item::findOne($itemId);

        if (!$item) {
            throw new NotFoundHttpException('Item not found');
        }

        $loop = Factory::create();

        $logger = new Logger();
        $writer = new Stream("php://output");
        $logger->addWriter($writer);

        $wsUrl = Yii::$app->params['wsURL'] . '?type=api_user&id=' . $user->id . '&auth_key=' . $user->auth_key;

        $client = new WebSocket($wsUrl, $loop, $logger);

        $client->on("connect", function() use ($turnOn, $client, $item, $loop){
            $client->send(Json::encode([
                'type' => $turnOn ? 'turnON' : 'turnOFF',
                'item_id' => $item->id,
            ]));

            $client->close();
        });

        $client->open();
        $loop->run();
    }

}
