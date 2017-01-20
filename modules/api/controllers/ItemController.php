<?php

namespace app\modules\api\controllers;

use app\models\Board;
use app\models\Item;
use app\models\Trigger;
use app\modules\api\components\WebSocketAPIBridge;
use Yii;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

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
            'rgb' => ['POST'],
            'rgb-mode' => ['POST'],
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
     * @param $item_id
     * @return array
     * @throws NotSupportedException
     */
    public function actionValue($item_id)
    {
        $item = $this->findItem($item_id);

        $board = $item->board;

        switch ($board->type) {
            case Board::TYPE_AREST:
                throw new NotSupportedException();
            case Board::TYPE_WEBSOCKET:
                $api = new WebSocketAPIBridge(Yii::$app->user->identity);

                return $api->getValue($item_id);
        }

        return [
            'success' => false,
        ];
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
                $api = new WebSocketAPIBridge(Yii::$app->user->identity);

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
     * @param int $item_id
     * @return array
     * @throws BadRequestHttpException
     * @throws NotSupportedException
     */
    public function actionTurnOff($item_id)
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
                $api = new WebSocketAPIBridge(Yii::$app->user->identity);

                if (!$api->turnOff($item_id)) {
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
     * @param int $item_id
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param bool $fade
     * @return array
     * @throws BadRequestHttpException
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function actionRgb($item_id, $red = 0, $green = 0, $blue = 0, $fade = false)
    {
        $item = $this->findItem($item_id);

        if ($item->type !== Item::TYPE_RGB) {
            throw new BadRequestHttpException();
        }

        $board = $item->board;

        switch ($board->type) {
            case Board::TYPE_AREST:
                throw new NotSupportedException();

            case Board::TYPE_WEBSOCKET:
                $api = new WebSocketAPIBridge(Yii::$app->user->identity);

                return [
                    'success' => $api->rgb($item_id, $red, $green, $blue, $fade),
                ];
            default:
                throw new ServerErrorHttpException();
        }
    }

    /**
     * @param int $item_id
     * @param string $mode
     * @param bool $start
     * @return array
     * @throws BadRequestHttpException
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function actionRgbMode($item_id, $mode, $start)
    {
        $item = $this->findItem($item_id);

        if ($item->type !== Item::TYPE_RGB) {
            throw new BadRequestHttpException('This item is not the RGB one');
        }

        if (!in_array($mode, Item::getModesArray())) {
            throw new InvalidParamException('Unknown mode');
        }

        $board = $item->board;

        switch ($board->type) {
            case Board::TYPE_AREST:
                throw new NotSupportedException();

            case Board::TYPE_WEBSOCKET:
                $api = new WebSocketAPIBridge(Yii::$app->user->identity);

                return [
                    'success' => $api->rgbMode($item_id, $mode, $start),
                ];
            default:
                throw new ServerErrorHttpException();
        }
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
