<?php

namespace app\assets;

use yii\web\AssetBundle;

class MDThemeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/theme.css',
        'css/ripples.css',
        'css/md.theme.css',
    ];
    public $js = [
        'js/ripples.js',
        'js/md.theme.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'app\assets\SnackbarjsAsset',
    ];
}
