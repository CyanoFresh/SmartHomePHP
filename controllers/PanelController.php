<?php

namespace app\controllers;

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

        return $this->render('index');
    }

}
