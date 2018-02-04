<?php

namespace snewer\images\assets;

use yii\web\AssetBundle;

class LaddaAsset extends AssetBundle
{

    public $sourcePath = '@snewer/images/static/ladda';

    public $css = [
        'ladda.min.css'
    ];

    public $js = [
        'spin.min.js',
        'ladda.min.js',
        'ladda.jquery.min.js',
    ];

}