<?php

namespace app\modules;

use yii\base\Module;

abstract class BaseModule extends Module
{
    /**
     * @return array
     */
    public static function getEventHandlers()
    {
        return [];
    }
}
