<?php

$params = require(__DIR__ . '/params.php');

return [
    'id' => 'basic',
    'name' => 'PROJECTNAME',
    'language' => 'ru',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@app/mail',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'login' => 'site/login',
                '<controller>/<id:\d+>/<action:(create|update|delete)>' => '<controller>/<action>',
                '<controller>/<id:\d+>' => '<controller>/view',
                '<controller>s' => '<controller>/index',
            ],
        ],
        'view' => [
            'class' => '\rmrevin\yii\minify\View',
            'minify_path' => '@webroot/assets',
            'js_position' => [\yii\web\View::POS_END],
            'force_charset' => 'UTF-8',
        ],
        'formatter' => [
            'dateFormat' => 'dd.MM.yyyy',
            'datetimeFormat' => 'php:d.m.Y H:i',
        ],
        /*'assetManager' => [
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [],
                    'js' => [],
                ],
            ],
        ],*/
    ],
    'params' => $params,
];
