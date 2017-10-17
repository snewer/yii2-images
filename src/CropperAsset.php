<?php

namespace snewer\images;

use yii\web\AssetBundle;

class CropperAsset extends AssetBundle
{

    public $sourcePath = '@snewer/images/static';

    public $css = ['cropper.min.css'];

    public $js = ['cropper.min.js'];

    public $depends = ['yii\web\JqueryAsset'];

}