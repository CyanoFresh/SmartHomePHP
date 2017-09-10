<?php

namespace app\modules\customModule;

use app\modules\server\events\ConnectionEvent;
use app\modules\server\events\ConnectionMessageEvent;
use app\modules\server\events\ServerEvent;

class EventHandler
{
    public static function onServerInit(ServerEvent $event)
    {
        $event->server->echo('Server initialized');
    }

    public static function onMessage(ConnectionMessageEvent $event)
    {
        $event->server->echo('New message from ' . $event->connection->resourceId . ': ' . $event->message);
    }
}
