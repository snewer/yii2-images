<?php

namespace snewer\images\models;

use Yii;
use Intervention\Image\ImageManager;

class ImageUpload
{

    /**
     * Минимальный размер стороны изображения.
     */
    const MIN_SIZE = 1;

    /**
     * Максимальный размер стороны изображения.
     */
    const MAX_SIZE = 10000;

    /**
     * @var \Intervention\Image\Image
     */
    public $image;

    public $model;

    /**
     * ImageUpload constructor.
     * @param $source
     * @param $driver
     */
    private function __construct($source, $driver)
    {
        $imageManager = new ImageManager(['driver' => $driver]);
        $this->image = $imageManager->make($source);
    }

    /**
     * @param $source
     * @param string $driver
     * @return self
     */
    public static function load($source, $driver = 'GD')
    {
        return new self($source, $driver);
    }

    /**
     * @param Image $image
     * @param string $driver
     * @return self
     */
    public static function extend(Image $image, $driver = 'GD')
    {
        return new self($image->source, $driver);
    }

    public function applyTool($configuration)
    {
        /* @var \snewer\images\tools\Tool $toolObject */
        $toolObject = Yii::createObject($configuration);
        $toolObject->init();
        $toolObject->process($this->image);
    }

    /**
     * Внимание! Возвращаемая модель не сохранена в базу данных.
     * @param string $storageName - Название хранилища, в которое необходимо загрузить изображение
     * @param bool $supportAC - Нужна ли поддержка альфа канала
     * @param integer $quality - Качество изображения
     * @return Image
     */
    public function upload($storageName, $supportAC = false, $quality = 90)
    {
        $image = new Image();
        $storageModel = ImageStorage::findOrCreateByName($storageName);
        $image->storage_id = $storageModel->id;
        // Была обнаружена проблема, когда md5 хэш файла не совпадал с хэшем файла,
        // который загружается в облачное хранилище по http. Trim решает проблему.
        $source = trim($this->image->encode($supportAC ? 'png' : 'jpeg', $quality));
        $path = $image->storage->upload($source, $supportAC ? 'png' : 'jpg');
        $image->path = $path;
        // https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity
        $image->integrity = 'sha384-' . base64_encode(hash('sha384', $source, true));
        $image->parent_id = null;
        $image->quality = $quality;
        $image->width = $this->image->width();
        $image->height = $this->image->height();
        $image->source = $source;
        $image->deleted = 0;
        return $image;
    }

}