<?php

namespace app\modules\api\controllers;

use app\modules\api\components\WebSocketAPI;
use Yii;
use yii\base\NotSupportedException;
use yii\rest\Controller;

class PanelController extends Controller
{
    /**
     * @inheritdoc
     */
    public function verbs()
    {
        return [
            'schedule-triggers' => ['POST'],
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
     * @return array|bool
     */
    public function actionScheduleTriggers()
    {
        $api = new WebSocketAPI(Yii::$app->user->identity);

        $result = $api->send([
            'type' => 'schedule-triggers',
        ]);

        return [
            'success' => $result,
        ];
    }

    /**
     * @return array|bool
     */
    public function actionUpdateItems()
    {
        $api = new WebSocketAPI(Yii::$app->user->identity);

        $result = $api->send([
            'type' => 'update-items',
        ]);

        return [
            'success' => $result,
        ];
    }
}
