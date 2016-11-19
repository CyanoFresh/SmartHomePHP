<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'app\components\ErrorAction',
                'layout' => Yii::$app->user->isGuest ? 'base' : 'main',
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
}
