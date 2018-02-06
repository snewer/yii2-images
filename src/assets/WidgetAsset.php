<?php

namespace snewer\images\assets;

use yii\web\AssetBundle;

class WidgetAsset extends AssetBundle
{

    public $sourcePath = '@snewer/images/static/widget';

    public $css = ['style.min.css'];

    public $js = ['script.min.js'];

    // Есть зависимости, но они подключаются отдельно в ImageUploadWidget.
    // Это сделано с той целью, что бы разработчики, использующие данный пакет,
    // могли указать на те же самые зависимости, но уже использующиеся в проекте.

}