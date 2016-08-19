<?php

use yii\helpers\ArrayHelper;

$params = [
    'adminEmail' => 'admin@example.com',
    'auth' => [
        'tokenExpireSec' => 2,
    ],
];

return ArrayHelper::merge($params, require 'params-local.php');
