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

    public $driver = 'GD';

    public function init()
    {
        parent::init();
        if ($this->imagesStoreStorageName === null) {
            throw new InvalidConfigException('Необходимо установить название хранилища для загрузки изображений \'Module::$imagesStoreStorageName\'.');
        }
        if ($this->previewsStoreStorageName === null) {
            $this->previewsStoreStorageName = $this->imagesStoreStorageName;
        }
        if (!in_array($this->driver, ['GD', 'Imagick'])) {
            throw new InvalidConfigException('Поддерживаются только следующие графические библиотеки: GD, Imagick.');
        }
    }

}