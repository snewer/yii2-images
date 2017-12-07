<?php

namespace snewer\images;

class Module extends \yii\base\Module
{

    public $storageComponentName = 'storage';

    public $imagesStoreStorageName = 'local_images';

    public $previewsStoreStorageName = 'local_images';

    public $previews = [];

    public $graphicsLibrary = 'GD';

    public $imageModel = 'snewer\images\models\Image';

}