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
        'yii\web\JqueryAsset',
    ];
}
