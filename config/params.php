<?php

use yii\helpers\ArrayHelper;

$params = [
    'adminEmail' => 'admin@example.com',
    'apiBaseUrl' => 'http://176.36.54.229',
];

return ArrayHelper::merge($params, require 'params-local.php');