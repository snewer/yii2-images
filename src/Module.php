<?php

namespace snewer\images;

use snewer\storage\StorageManager;
use yii\filters\AccessControl;
use yii\base\InvalidConfigException;

/**
 * Class Module
 * @package snewer\images
 * @property \snewer\storage\StorageManager $storage
 */
class Module extends \yii\base\Module
{
    public static $_id;

    public $imagesStoreBucketName;

    public $previewsStoreBucketName;

    public $imagesQuality = 85;

    public $previewsQuality = 85;

    public $supportPng = true;

    public $forceUseWebp = false;

    public $controllerAccess;

    public $buckets = [];

    public $previewsMap = [];

    public function init()
    {
        parent::init();
        if ($this->imagesStoreBucketName === null) {
            throw new InvalidConfigException('Необходимо установить название хранилища для загрузки изображений \'Module::$imagesStoreStorageName\'.');
        }
        if ($this->previewsStoreBucketName === null) {
            $this->previewsStoreBucketName = $this->imagesStoreBucketName;
        }

        $this->setComponents([
            'storage' => [
                'class' => StorageManager::class,
                'buckets' => $this->buckets,
            ]
        ]);

        $this->imagesQuality = min(max(ceil($this->imagesQuality), 10), 100);
        $this->previewsQuality = min(max(ceil($this->previewsQuality), 10), 100);
        if ($this->controllerAccess === null) {
            $this->controllerAccess = [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ];
        }
    }
}