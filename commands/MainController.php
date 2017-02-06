<?php

namespace app\commands;

use app\servers\CoreServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use yii\console\Controller;

class MainController extends Controller
{
    public function actionIndex($port = 8081)
    {
        echo "Starting Core WS Server on port $port..." . PHP_EOL;

        $loop = Factory::create();

        $socket = new Server($loop);
        $socket->listen($port, '0.0.0.0');

        $server = new IoServer(
            new HttpServer(
                new WsServer(
                    new CoreServer($loop)
                )
            ),
            $socket,
            $loop
        );

        $server->run();
    }
}
