<?php

namespace app\modules\admin;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\admin\controllers';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return Yii::$app->user->identity->isAdmin;
                        },
                    ],
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Yii::$app->view->params['breadcrumbs'][] = ['label' => 'Админпанель', 'url' => ['/admin/default/index']];
    }
}
