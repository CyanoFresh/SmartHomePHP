<?php

namespace app\modules\customModule;

use app\modules\server\events\ConnectionEvent;
use app\modules\server\events\ConnectionMessageEvent;
use app\modules\server\events\ServerEvent;

class EventHandler
{
    public static function onServerInit(ServerEvent $event)
    {
        echo 'Server initialized' . PHP_EOL;
    }

    public static function onMessage(ConnectionMessageEvent $event)
    {
        echo 'New message from ' . $event->connection->resourceId . ': ' . $event->message . PHP_EOL;

        $event->server->sendAllUsers([
            'message',
            [
                'from_id' => $event->connection->resourceId,
                'text' => $event->message,
            ],
        ]);
    }
}
