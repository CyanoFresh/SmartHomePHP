<?php

namespace app\assets\vendors;

use yii\web\AssetBundle;

class SpectrumAsset extends AssetBundle
{
    public $sourcePath = '@bower/spectrum';
    public $js = [
        'spectrum.js',
    ];
    public $css = [
        'spectrum.css',
    ];
    public $publishOptions = [
        'only' => [
            '*.js',
            '*.css',
        ],
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
