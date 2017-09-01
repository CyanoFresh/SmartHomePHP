<?php

namespace app\modules\server\events;

use app\modules\server\components\CoreServer;
use yii\base\Event;

class ServerEvent extends Event
{
    /**
     * @var CoreServer
     */
    public $server;
}
