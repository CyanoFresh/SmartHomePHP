<?php

namespace app\assets;

use yii\web\AssetBundle;

class PNotifyAsset extends AssetBundle
{
    public $sourcePath = '@bower/pnotify/dist';
    public $js = [
        'pnotify.js',
    ];
    public $css = [
        'pnotify.css',
    ];
}
