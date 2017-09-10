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

    /**
     * @param string $message
     * @param bool $prependDate
     * @param bool $appendEol
     */
    public function echo(string $message, $prependDate = true, $appendEol = true)
    {
        $result = '';

        if ($prependDate) {
            $ms = explode(' ', microtime());
            $ms = round((float)$ms[0], 3) * 1000;

            $result .= '[' . date('Y-m-d h:i:s', time()) . '.' . $ms . '] ';
        }

        $result .= $message;

        if ($appendEol) {
            $result .= PHP_EOL;
        }

        echo $result;
    }

}
