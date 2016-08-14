<?php

namespace app\controllers;

class PanelController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
