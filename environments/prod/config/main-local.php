<?php

return [
    'components' => [
        'mailer' => [
            'useFileTransport' => false,
        ],
        'request' => [
            'cookieValidationKey' => '',
        ],
        'view' => [
            'enableMinify' => true,
        ],
    ],
];