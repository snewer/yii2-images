<?php

namespace snewer\images\assets;

use yii\web\AssetBundle;

class MagnificPopupAsset extends AssetBundle
{

    public $sourcePath = '@snewer/images/static/magnific-popup';

    public $css = [
        'magnific-popup.css'
    ];

    public $js = [
        'jquery.magnific-popup.min.js'
    ];

}