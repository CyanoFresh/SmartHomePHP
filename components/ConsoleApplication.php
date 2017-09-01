<?php

namespace app\components;

use app\modules\BaseModule;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\console\Application;
use yii\helpers\VarDumper;

/**
 * Class ConsoleApplication
 *
 * @property EventManager $eventManager
 *
 * @package app\components
 */
class ConsoleApplication extends Application
{
    public function init()
    {
        parent::init();

        $this->eventManager->registerModulesHandlers($this->modules);
    }
}
