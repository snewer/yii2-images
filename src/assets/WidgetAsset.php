<?php

namespace snewer\images\assets;

use yii\web\AssetBundle;

class WidgetAsset extends AssetBundle
{

    public $sourcePath = '@snewer/images/static';

    public $css = [
        'style.css',
    ];
    public $js = [
        'script.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];

}