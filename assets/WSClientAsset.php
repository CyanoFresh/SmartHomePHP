<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class WSClientAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/wsclient.css',
    ];
    public $js = [
        'js/wsclient.js',
    ];
    public $depends = [
        'app\assets\SpectrumAsset',
        'app\assets\AppAsset',
        'yii\web\JqueryAsset',
    ];
}
