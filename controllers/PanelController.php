<?php

namespace app\controllers;

use app\models\Room;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\View;

class PanelController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->view->registerJs('
            var wsURL = "' . Yii::$app->params['wsURL']
            . '/?type=user&id=' . Yii::$app->user->identity->id
            . '&auth_token=' . Yii::$app->user->identity->getAuthToken()
            . '";
        ', View::POS_HEAD);

        $roomModels = Room::find()->all();

        return $this->render('index', [
            'roomModels' => $roomModels,
        ]);
    }

}
