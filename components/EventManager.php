<?php

namespace app\components;

use app\modules\BaseModule;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;

class EventManager
{
    /**
     * @param string $name
     * @param Event|null $event
     */
    public function trigger($name, $event = null)
    {
        Yii::$app->trigger($name, $event);
    }

    /**
     * @param array $handlers
     * @throws InvalidConfigException
     */
    public function registerHandlers($handlers)
    {
        foreach ($handlers as $handler) {
            if (count($handler) === 2) {
                \Yii::$app->on($handler[0], $handler[1]);
            } elseif (count($handler) === 3) {
                Event::on($handler[0], $handler[1], $handler[2]);
            } else {
                throw new InvalidConfigException('Invalid event configuration');
            }
        }
    }

    /**
     * @param array $modules
     */
    public function registerModulesHandlers($modules)
    {
        foreach ($modules as $module) {
            if (!is_array($module)) {
                continue;
            }

            /** @var BaseModule $moduleClass */
            $moduleClass = $module['class'];

            if (method_exists($moduleClass, 'getEventHandlers')) {
                $eventHandlers = $moduleClass::getEventHandlers();

                $this->registerHandlers($eventHandlers);
            }
        }
    }
}
