<?php

namespace app\assets;

use yii\web\AssetBundle;

class HandlebarsAsset extends AssetBundle
{
    public $sourcePath = '@bower/handlebars';
    public $js = [
        'handlebars.js',
    ];
    public $publishOptions = [
        'only' => [
            '*.js',
        ]
    ];
}
