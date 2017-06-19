<?php

namespace app\assets\vendors;

use yii\web\AssetBundle;

class JSCookieAsset extends AssetBundle
{
    public $sourcePath = '@bower/js-cookie';
    public $js = [
        'src/js.cookie.js',
    ];
    public $publishOptions = [
        'only' => [
            '*.js',
        ]
    ];
}
