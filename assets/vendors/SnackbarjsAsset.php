<?php

namespace app\assets\vendors;

use yii\web\AssetBundle;

class SnackbarjsAsset extends AssetBundle
{
    public $sourcePath = '@bower/snackbarjs';
    public $js = [
        'dist/snackbar.min.js',
    ];
    public $css = [
        'dist/snackbar.css',
        'themes-css/material.css',
    ];
    public $publishOptions = [
        'only' => [
            '*.js',
            '*.css',
            '*.js.*',
            '*.css.*',
        ]
    ];
}
