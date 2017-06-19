<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/theme.css',
        'css/ripples.css',
        'css/md.theme.css',
        'css/site.css',
    ];
    public $js = [
        'js/ripples.js',
        'js/md.theme.js',
        'js/site.js',
    ];
    public $depends = [
        'app\assets\vendors\FontsAsset',
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\web\YiiAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
        'app\assets\vendors\SnackbarjsAsset',
        'app\assets\vendors\SpectrumAsset',
//        'dmstr\web\AdminLteAsset',
//        'shifrin\noty\NotyAsset',
//        'app\assets\MDThemeAsset',
//        'app\assets\vendors\JSCookieAsset',
//        'app\assets\vendors\ChartjsAsset',
    ];
}
