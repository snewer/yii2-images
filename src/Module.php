<?php

namespace snewer\images;

class Module extends \yii\base\Module
{

    public $storageComponentName = 'storage';

    public $imagesStoreStorageName = 'local_images';

    public $imagesQuality = 100;

    public $previewsStoreStorageName = 'local_images';

    public $previewsQuality = 90;

    public $previews = [];

    public $graphicsLibrary = 'GD';

    public $imageModel = 'snewer\images\models\Image';

}