<?php

namespace app\assets\vendors;

use yii\web\AssetBundle;

class ChartjsAsset extends AssetBundle
{
    public $sourcePath = '@bower/chart.js/dist';
    public $js = [
        'Chart.min.js',
    ];
    public $publishOptions = [
        'only' => [
            '*.js',
        ]
    ];
}
