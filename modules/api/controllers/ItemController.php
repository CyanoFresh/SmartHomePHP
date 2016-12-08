<?php

namespace app\modules\api\controllers;

use app\models\Board;
use app\models\Item;
use app\modules\api\components\WebSocketAPI;
use Yii;
use yii\base\NotSupportedException;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class ItemController extends Controller
{
    /**
     * @inheritdoc
     */
    public function verbs()
    {
        return [
            'turn-on' => ['POST'],
            'turn-off' => ['POST'],
            'value' => ['GET'],
        ];
    }

    /**
     * @throws NotSupportedException
     */
    public function actionIndex()
    {
        throw new NotSupportedException();
    }

    /**
     * @param int $item_id
     * @return array
     * @throws BadRequestHttpException
     * @throws NotSupportedException
     */
    public function actionTurnOn($item_id)
    {
        $item = $this->findItem($item_id);

        if ($item->type !== Item::TYPE_SWITCH) {
            throw new BadRequestHttpException();
        }

        $board = $item->board;

        switch ($board->type) {
            case Board::TYPE_AREST:
                throw new NotSupportedException();
                break;
            case Board::TYPE_WEBSOCKET:
                $api = new WebSocketAPI(Yii::$app->user->identity);

                if (!$api->turnOn($item_id)) {
                    return [
                        'success' => false,
                    ];
                }

                break;
        }

        return [
            'success' => true,
        ];
    }

    /**
     * @param int $id
     * @return Item
     * @throws NotFoundHttpException
     */
    private function findItem($id)
    {
        $item = Item::findOne($id);

        if (!$item) {
            throw new NotFoundHttpException();
        }

        return $item;
    }
}
