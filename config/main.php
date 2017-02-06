<?php

$params = require(__DIR__ . '/params.php');

return [
    'id' => 'solomaha-home',
    'name' => 'Solomaha Home',
    'language' => 'ru',
    'sourceLanguage' => 'ru',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/auth/login'],
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
                '' => 'panel/index',
                '<controller>' => '<controller>/index',
                'admin/<controller>/<id:\d+>/<action:(create|update|delete)>' => 'admin/<controller>/<action>',
                'admin/<controller>/<id:\d+>' => 'admin/<controller>/view',
                'admin/<controller>s' => 'admin/<controller>/index',
            ],
        ],
        'view' => [
            'class' => 'rmrevin\yii\minify\View',
            'minify_path' => '@webroot/assets',
            'js_position' => [\yii\web\View::POS_END],
            'force_charset' => 'UTF-8',
        ],
        'formatter' => [
//            'dateFormat' => 'dd.MM.yyyy',
//            'datetimeFormat' => 'php:d.m.Y H:i',
            'defaultTimeZone' => 'Europe/Kiev',
            'timeZone' => 'Europe/Kiev',
        ],
        'assetManager' => [
            'bundles' => [
                'dmstr\web\AdminLteAsset' => [
//                    'skin' => 'skin-purple',
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [],
//                    'js' => [],
                ],
            ],
        ],
    ],
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
        'datecontrol' => [
            'class' => '\kartik\datecontrol\Module'
        ],
    ],
    'params' => $params,
    'defaultRoute' => ['panel/index'],
];
