<?php

use yii\helpers\ArrayHelper;

$params = [
    'adminEmail' => 'admin@example.com',
];

return ArrayHelper::merge($params, require 'params-local.php');