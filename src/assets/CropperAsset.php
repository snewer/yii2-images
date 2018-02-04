<?php

namespace snewer\images\assets;

use yii\web\AssetBundle;

class CropperAsset extends AssetBundle
{

    public $sourcePath = '@snewer/images/static/cropper';

    public $css = [
        'cropper.min.css'
    ];

    public $js = [
        'cropper.min.js'
    ];

}