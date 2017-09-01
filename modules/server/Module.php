<?php

namespace app\modules\server;

use Yii;
use yii\console\Application;

/**
 * server module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\server\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof Application) {
            $this->controllerNamespace = 'app\modules\server\commands';
        }
    }
}
