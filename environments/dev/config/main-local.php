<?php

return [
    'bootstrap' => ['debug', 'gii'],
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
        ],
        'request' => [
            'cookieValidationKey' => '',
        ],
        'view' => [
            'enableMinify' => false,
        ],
    ],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
        ],
        'gii' => [
            'class' => 'yii\gii\Module',
            'generators' => [
                'crud' => [
                    'class' => 'yii\gii\generators\crud\Generator',
                    'templates' => [
                        'myCrud' => '@app/giiTemplates/crud/my',
                    ]
                ]
            ],
        ],
    ],
];