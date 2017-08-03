<?php

namespace app\assets;

use yii\web\AssetBundle;

class PanelAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/panel.css',
    ];
    public $js = [
        'js/panel.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
        'app\assets\vendors\HandlebarsAsset',
        'app\assets\vendors\SpectrumAsset',
        'app\assets\vendors\ChartjsAsset',
        'app\assets\vendors\MasonryAsset',
        'yii\web\JqueryAsset',
    ];
}
