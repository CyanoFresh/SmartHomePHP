<?php

namespace app\modules\server\components;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use Yii;
use yii\base\Component;
use yii\base\Event;
use yii\helpers\Json;

abstract class BaseServer extends Component implements MessageComponentInterface
{
    /**
     * @var LoopInterface
     */
    public $loop;

    /**
     * @param string $name
     * @param Event|null $event
     */
    public function trigger($name, Event $event = null)
    {
        Yii::$app->eventManager->trigger($name, $event);
    }

}
