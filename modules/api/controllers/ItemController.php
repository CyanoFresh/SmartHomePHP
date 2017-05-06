<?php

namespace app\modules\api\controllers;

use app\models\Board;
use app\models\History;
use app\models\Item;
use app\modules\api\components\WebSocketAPIBridge;
use Yii;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;

class ItemController extends ActiveController
{
    public $modelClass = 'app\models\Item';

    /**
     * @inheritdoc
     */
    public function verbs()
    {
        return [
            'turn-on' => ['POST'],
            'turn-off' => ['POST'],
            'rgb' => ['POST'],
            'value' => ['GET'],
        ];
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
     * @param string $mode
     * @param int $fade_time
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $color_time
     * @return array
     * @throws BadRequestHttpException
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     * @throws UnprocessableEntityHttpException
     * @internal param bool $fade
     */
    public function actionRgb(
        $item_id,
        $mode,
        $fade_time = null,
        $red = null,
        $green = null,
        $blue = null,
        $color_time = null
    ) {
        $item = $this->findItem($item_id);

        if ($item->type !== Item::TYPE_RGB) {
            throw new BadRequestHttpException('It is not a RGB Item');
        }

        if (!in_array($mode, Item::getRGBModesArray())) {
            throw new BadRequestHttpException('Invalid mode');
        }

        if (!isset($fade_time) or $fade_time === null) {
            $fade_time = Yii::$app->params['items']['rgb']['fade-time'];
        } elseif ($fade_time < 0) {
            $fade_time = 0;
        }

        $sendParameters = [
            'type' => 'rgb',
            'item_id' => $item_id,
            'mode' => $mode,
            'fade_time' => $fade_time,
        ];

        if ($mode === Item::RGB_MODE_STATIC or $mode === Item::RGB_MODE_FADE) {
            if (!isset($red) or $red === null) {
                throw new UnprocessableEntityHttpException('Missing red parameter');
            }

            if (!isset($green) or $green === null) {
                throw new UnprocessableEntityHttpException('Missing green parameter');
            }

            if (!isset($blue) or $blue === null) {
                throw new UnprocessableEntityHttpException('Missing blue parameter');
            }

            $sendParameters['red'] = $red;
            $sendParameters['green'] = $green;
            $sendParameters['blue'] = $blue;
        }

        if ($mode === Item::RGB_MODE_WAVE or $mode === Item::RGB_MODE_FADE) {
            if (!isset($color_time) or $color_time === null) {
                throw new UnprocessableEntityHttpException('Missing color_time parameter');
            }

            $sendParameters['color_time'] = $color_time;
        }

        $board = $item->board;

        switch ($board->type) {
            case Board::TYPE_AREST:
                throw new NotSupportedException();

            case Board::TYPE_WEBSOCKET:
                $api = new WebSocketAPIBridge(Yii::$app->user->identity);

                return [
                    'success' => $api->send($sendParameters),
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

        if (!in_array($mode, Item::getRGBModesArray())) {
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
     * @param int $item_id
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionChartData($item_id)
    {
        $item = $this->findItem($item_id);

        if (!in_array($item->type, [
            Item::TYPE_VARIABLE,
            Item::TYPE_VARIABLE_HUMIDITY,
            Item::TYPE_VARIABLE_LIGHT,
            Item::TYPE_VARIABLE_TEMPERATURE
        ])
        ) {
            throw new BadRequestHttpException('This item is not the variable one');
        }

        $historyModels = History::find()
            ->where([
                'type' => History::TYPE_ITEM_VALUE,
                'item_id' => $item->id,
            ])
            ->andWhere(['>=', 'commited_at', time() - 21600])
            ->orderBy('commited_at DESC')
            ->all();

        $data = [];

        foreach ($historyModels as $historyModel) {
            $data[$historyModel->commited_at] = $historyModel->value;
        }

        return [
            'success' => true,
            'data' => $data,
            'item' => $item,
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
