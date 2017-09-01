<?php

namespace app\modules\server\events;

use Ratchet\WebSocket\Version\RFC6455\Connection;

class ConnectionEvent extends ServerEvent
{
    /**
     * @var Connection
     */
    public $connection;
}
