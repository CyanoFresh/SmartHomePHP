<?php

namespace app\modules\server\events;

class ConnectionErrorEvent extends ConnectionEvent
{
    /**
     * @var \Exception
     */
    public $exception;
}
