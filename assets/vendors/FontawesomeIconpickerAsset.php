<?php

namespace app\assets\vendors;

use yii\web\AssetBundle;

class FontawesomeIconpickerAsset extends AssetBundle
{
    public $sourcePath = '@bower/fontawesome-iconpicker/dist';
    public $js = [
        'js/fontawesome-iconpicker.min.js',
    ];
    public $css = [
        'css/fontawesome-iconpicker.min.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
