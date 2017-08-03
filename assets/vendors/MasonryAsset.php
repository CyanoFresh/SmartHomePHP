<?php

namespace app\assets\vendors;

use yii\web\AssetBundle;

class MasonryAsset extends AssetBundle
{
    public $sourcePath = '@bower/masonry/dist';
    public $js = [
        'masonry.pkgd.min.js',
    ];
    public $publishOptions = [
        'only' => [
            'masonry.pkgd.min.js',
        ]
    ];
}
