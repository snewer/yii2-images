<?php

namespace snewer\images;

use Yii;
use yii\filters\AccessControl;
use yii\base\InvalidConfigException;

/**
 * Class Module
 * @package snewer\images
 * @property \snewer\storage\StorageManager $storage
 */
class Module extends \yii\base\Module
{

    /**
     * @var string|\snewer\storage\StorageManager
     */
    private $_storage = 'storage';

    public $imagesStoreBucketName;

    public $imagesQuality = 100;

    public $previewsStoreBucketName;

    public $previewsQuality = 80;

    public $previews = [];

    public $driver = 'GD';

    public $controllerAccess;

    public function init()
    {
        parent::init();
        if ($this->imagesStoreBucketName === null) {
            throw new InvalidConfigException('Необходимо установить название хранилища для загрузки изображений \'Module::$imagesStoreStorageName\'.');
        }
        if ($this->previewsStoreBucketName === null) {
            $this->previewsStoreBucketName = $this->imagesStoreBucketName;
        }
        if (!in_array($this->driver, ['GD', 'Imagick'])) {
            throw new InvalidConfigException('Поддерживаются только следующие графические библиотеки: GD, Imagick.');
        }
        $this->imagesQuality = min(max(ceil($this->imagesQuality), 10), 100);
        $this->previewsQuality = min(max(ceil($this->previewsQuality), 10), 100);
        if ($this->controllerAccess === null) {
            $this->controllerAccess = [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ];
        }
    }

    public function setStorage($value)
    {
        if (is_string($value)) {
            $this->_storage = $value;
        } else {
            $this->_storage = Yii::createObject($value);
        }
    }

    /**
     * @return \snewer\storage\StorageManager
     */
    public function getStorage()
    {
        if (is_string($this->_storage)) {
            return Yii::$app->get($this->_storage, true);
        } else {
            return $this->_storage;
        }
    }

}