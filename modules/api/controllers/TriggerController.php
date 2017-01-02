<?php

namespace app\modules\api\controllers;

use app\models\Trigger;
use app\modules\api\components\WebSocketAPI;
use Yii;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class TriggerController extends Controller
{
    /**
     * @inheritdoc
     */
    public function verbs()
    {
        return [
            'trig' => ['POST'],
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
     * @param int $trigger_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionTrig($trigger_id)
    {
        $trigger = $this->findTrigger($trigger_id);

        if ($trigger->type !== Trigger::TYPE_MANUAL) {
            throw new InvalidParamException('This Trigger cannot be triggered by API call');
        }

        $api = new WebSocketAPI(Yii::$app->user->identity);

        return [
            'success' => $api->trig($trigger_id),
        ];
    }

    /**
     * @param int $id
     * @return Trigger
     * @throws NotFoundHttpException
     */
    private function findTrigger($id)
    {
        $item = Trigger::findOne($id);

        if (!$item) {
            throw new NotFoundHttpException('Trigger was not found');
        }

        return $item;
    }
}
