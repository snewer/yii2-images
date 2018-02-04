<?php

namespace snewer\images\models;

use Yii;
use snewer\images\ModuleTrait;
use Intervention\Image\ImageManager;

class ImageUpload
{

    use ModuleTrait;

    /**
     * @var \Intervention\Image\Image
     */
    public $image;

    /**
     * @var Image
     */
    public $model;

    /**
     * ImageUpload constructor.
     * @param $source
     */
    private function __construct($source)
    {
        $imageManager = new ImageManager(['driver' => $this->getModule()->driver]);
        $this->image = $imageManager->make($source);
    }

    /**
     * Инициализирует загрузчик из
     * @param $source
     * @return self
     */
    public static function load($source)
    {
        return new self($source);
    }

    /**
     * Инициализирует загрузчик изображения из существующей модели.
     * @param Image $image
     * @return self
     */
    public static function extend(Image $image)
    {
        $obj = new self($image->source);
        $obj->model = $image;
        return $obj;
    }

    /**
     * Применяет к изображению объекты типа \snewer\images\tools\Tool.
     * @param $configuration
     */
    public function applyTool($configuration)
    {
        $toolObject = is_object($configuration) ? $configuration : Yii::createObject($configuration);
        $toolObject->init();
        $toolObject->process($this->image);
    }

    /**
     * Внимание! Метод не сохраняет возвращаемую модель в базу данных.
     * @param string $storageName - Название хранилища, в которое необходимо загрузить изображение
     * @param bool $supportAC - Нужна ли поддержка альфа канала
     * @param integer $quality - Качество изображения
     * @return Image
     */
    public function upload($storageName, $supportAC, $quality)
    {
        $quality = min(max($quality, 30), 100);
        $image = new Image();
        $storageModel = ImageBucket::findOrCreateByName($storageName);
        $image->bucket_id = $storageModel->id;
        $source = trim($this->image->encode($supportAC ? 'png' : 'jpeg', $quality));
        $path = $image->bucket->upload($source, $supportAC ? 'png' : 'jpg');
        $image->path = $path;
        $image->integrity = 'sha384-' . base64_encode(hash('sha384', $source, true));
        $image->quality = $quality;
        $image->width = $this->image->width();
        $image->height = $this->image->height();
        $image->source = $source;
        $image->deleted = 0;
        return $image;
    }

}