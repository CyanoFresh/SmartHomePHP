<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class ProfileController extends Controller
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

    public function actionIndex($id = false)
    {
        $user = $id ? User::findOne($id) : Yii::$app->user->identity;

        return $this->render('index', [
            'user' => $user,
        ]);
    }

    public function actionEdit()
    {
        $user = Yii::$app->user->identity;
        $user->scenario = User::SCENARIO_UPDATE;

        if ($user->load(Yii::$app->request->post())) {
            $user->setPassword($user->password);
            $user->generateAuthKey();

            if ($user->save()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('edit', [
            'user' => $user,
        ]);
    }
}
