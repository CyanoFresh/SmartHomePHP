<?php

namespace app\modules\admin\controllers;

use app\models\Setting;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SettingController extends Controller
{
    public function actionIndex()
    {
        $models = Setting::find()->all();

        return $this->render('index', [
            'models' => $models,
        ]);
    }

    public function actionSave()
    {
        $settings = Yii::$app->request->post('Settings');

        foreach ($settings as $key => $value) {
            $model = Setting::findOne(['key' => $key]);

            if (!$model) {
                throw new NotFoundHttpException();
            }

            $model->value = $value;
            $model->save();
        }

        return $this->redirect(['index']);
    }

}
