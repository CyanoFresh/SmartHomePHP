<?php

namespace app\controllers;

use app\models\Room;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
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
        $this->layout = 'main-no-content';

        $this->view->registerJs('
            var wsURL = "' . Yii::$app->params['wsURL']
            . '/?type=user&id=' . Yii::$app->user->identity->id
            . '&auth_token=' . Yii::$app->user->identity->getAuthToken()
            . '";
            var itemValueChartUrl = "' . Url::to(['/api/item/chart-data', 'access-token' => Yii::$app->user->identity->api_key]) . '"
        ', View::POS_HEAD);

        $roomModels = Room::find()->orderBy('sort_order')->all();

        return $this->render('index', [
            'roomModels' => $roomModels,
        ]);
    }

}
