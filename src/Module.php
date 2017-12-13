<?php

namespace snewer\images;

use yii\base\InvalidConfigException;

class Module extends \yii\base\Module
{

    public $storageComponentName = 'storage';

    public $imagesStoreStorageName;

    public $imagesQuality = 100;

    public $previewsStoreStorageName;

    public $previewsQuality = 90;

    public $previews = [];

    public $graphicsLibrary = 'GD';

    public $imageModel = 'snewer\images\models\Image';

    public function init()
    {
        parent::init();
        if ($this->imagesStoreStorageName === null) {
            throw new InvalidConfigException('Необходимо установить название хранилища для загрузки изображений \'Module::$imagesStoreStorageName\'.');
        }
        if ($this->previewsStoreStorageName === null) {
            $this->previewsStoreStorageName = $this->imagesStoreStorageName;
        }
        if ($this->imageModel === null) {
            throw new InvalidConfigException('Необходимо задать модель изображений.');
        }
        if (!in_array($this->graphicsLibrary, ['GD', 'Imagick'])) {
            throw new InvalidConfigException('Поддерживаются только следующие графические библиотеки: GD, Imagick.');
        }
    }

}