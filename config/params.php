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
    ],
    'telegramBotApiKey' => '',
    'telegramBotChatId' => '',
];

return ArrayHelper::merge($params, require 'params-local.php');
