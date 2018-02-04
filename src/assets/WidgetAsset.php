<?php

namespace snewer\images\assets;

use yii\web\AssetBundle;

class WidgetAsset extends AssetBundle
{

    public $sourcePath = '@snewer/images/static/widget';

    public $css = [
        'style.css',
    ];
    public $js = [
        'script.js',
    ];

}