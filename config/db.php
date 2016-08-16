<?php

use yii\helpers\ArrayHelper;

$db = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=smarthome',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];

return ArrayHelper::merge($db, require 'db-local.php');
