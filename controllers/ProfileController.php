<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\web\Controller;

class ProfileController extends Controller
{
    public function actionIndex($id = false)
    {
        $user = $id ? User::findOne($id) : Yii::$app->user->identity;

        return $this->render('index', [
            'user' => $user,
        ]);
    }
}
