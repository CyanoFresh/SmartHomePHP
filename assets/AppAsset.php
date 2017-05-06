<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://fonts.googleapis.com/css?family=Product+Sans',
        'css/site.css',
    ];
    public $js = [
        'js/site.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
//        'dmstr\web\AdminLteAsset',
//        'shifrin\noty\NotyAsset',
        'app\assets\MDThemeAsset',
//        'app\assets\JSCookieAsset',
//        'app\assets\ChartjsAsset',
    ];
}
