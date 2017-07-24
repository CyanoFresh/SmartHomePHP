<?php

use yii\helpers\ArrayHelper;

$params = [
    'adminEmail' => 'admin@example.com',
    'wsURL' => 'ws://192.168.1.111:8081',
    'localWSURL' => 'ws://127.0.0.1:8081',
    'maxLoginFailCount' => 5,
    'loginFailTimeout' => 600,
    'server' => [
        'connectionCheckTimeout' => 180,
        'connectionCheckMaxIteration' => 2,
        'maxLastPingTimeout' => 600,
    ],
    'items' => [
        'rgb' => [
            'fade-time' => 3000,
            'color-time' => 3000,
            'red' => 0,
            'green' => 150,
            'blue' => 150,
        ],
        'chart' => [
            'forLast' => 7200,
        ],
    ],
    'telegramBotApiKey' => '',
    'telegramBotChatId' => '',
];

return ArrayHelper::merge($params, require 'params-local.php');
