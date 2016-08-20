<?php

namespace app\servers;

use app\components\ApiHelper;
use app\models\History;
use app\models\Item;
use app\models\User;
use Guzzle\Http\QueryString;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\Version\RFC6455\Connection;
use React\EventLoop\LoopInterface;
use Yii;
use yii\helpers\Json;
use yii\helpers\VarDumper;

/**
 * Class Panel
 *
 * WebSockets handler
 *
 * @package app\components
 * @author CyanoFresh <cyanofresh@gmail.com>
 */
class Test implements MessageComponentInterface
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var ConnectionInterface[]
     */
    protected $clients;

    /**
     * @var ConnectionInterface[]
     */
    protected $boards;

    /**
     * Panel constructor.
     *
     * Init variables and preparing
     *
     * @param LoopInterface $loop
     */
    public function __construct($loop)
    {
        $this->loop = $loop;
        $this->clients = [];
        $this->boards = [];

        $this->log('Server started!');
    }

    /**
     * @inheritdoc
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->log('Connecting new client....');

        /** @var QueryString $query */
        $query = $conn->WebSocket->request->getQuery();

        $type = $query->get('type');

        if (!$type) {
            return $conn->send('Need more info!');
        }

        if ($type == 'board') {
            $id = $query->get('id');

            $conn->type = 'board';
            $conn->id = $id;
            $this->boards[$id] = $conn;

            $this->sendAll("Board connected. BoardID: $id");
            $this->log("Board connected. BoardID: $id");

            $this->loop->addPeriodicTimer(5, function () use ($conn) {
                $conn->send('switch');
            });

            return true;
        } elseif ($type == 'user') {
            $id = $query->get('id');
            $username = $query->get('username');

            $conn->type = 'user';
            $conn->id = $id;
            $conn->username = $username;

            $this->clients[$id] = $conn;

            $this->log("New user. Username: $username; ID: $id");

            return true;
        }

        return false;
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if ($from->type == 'user') {
            $data = Json::decode($msg);

            if ($data['type'] == 'switch') {
                $boardID = $data['boardID'];
                $this->boards[$boardID]->send(1);
            }
            $this->log("New message from [{$from->username}]: $msg");
        } elseif (!$from->type) {
            $this->log("New message from [{$from->id}]: $msg");
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->sendAll("Disconnected []");
        $this->log("Disconnected []");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // Logging
        echo "Error: {$e->getMessage()} in file {$e->getFile()} at line {$e->getLine()}" . PHP_EOL;

        // Close connection
        $conn->close();
    }

    public function sendAll($text)
    {
        foreach ($this->clients as $client) {
            $client->send($text);
        }
    }

    public function log($msg)
    {
        echo $msg . PHP_EOL;
    }
}
