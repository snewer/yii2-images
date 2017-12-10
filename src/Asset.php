<?php

namespace snewer\images;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{

    public $sourcePath = '@snewer/images/static';

    public $css = [
        'style.css',
    ];
    public $js = [
        'script.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        //'common\assets\CropperAsset',
        //'common\assets\LaddaAsset',
        //'common\assets\ViewerAsset',
    ];

}