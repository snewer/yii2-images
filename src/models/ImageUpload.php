<?php

namespace snewer\images\models;

use Yii;
use snewer\images\ModuleTrait;
use Intervention\Image\ImageManager;

class ImageUpload
{

    use ModuleTrait;

    /**
     * @var ImageManager
     */
    private $imageManager;

    /**
     * @var \Intervention\Image\Image
     */
    public $image;

    /**
     * ImageUpload constructor.
     * @param $source
     */
    private function __construct($source)
    {
        if ($source instanceof \Intervention\Image\Image) {
            $this->image = $source;
        } else {
            $this->imageManager = new ImageManager(['driver' => 'Imagick']);
            $this->image = $this->imageManager->make($source);
        }
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
        return new self($image->source);
    }

    /**
     * Применяет к изображению объекты типа \snewer\images\tools\Tool.
     * @param $configuration
     */
    public function applyTool($configuration)
    {
        $toolObject = is_object($configuration) ? $configuration : Yii::createObject($configuration);
        $toolObject->image = $this->image;
        $toolObject->init();
        $toolObject->process();
    }

    /**
     * Внимание! Метод не сохраняет возвращаемую модель в базу данных.
     * @param null|integer $parentId
     * @param null|string $previewHash
     * @return Image
     */
    public function upload($parentId = null, $previewHash = null)
    {
        if ($parentId === null) {
            $storageName = $this->getModule()->imagesStoreBucketName;
            $quality = $this->getModule()->imagesQuality;
        } else {
            $storageName = $this->getModule()->previewsStoreBucketName;
            $quality = $this->getModule()->previewsQuality;
        }

        if ($this->getModule()->forceUseWebp) {
            $format = 'webp';
        } elseif ($this->getModule()->supportPng) {
            if ($parentId !== null || $this->image->getCore()->getImageAlphaChannel() === \Imagick::ALPHACHANNEL_UNDEFINED) {
                // Когда загружается прозрачное изображение и сохраняется как JPEG, то его фон заливается черным цветом.
                // Для решения данной проблемы накладываем его на полотно с белым фоном.
                //$jpgImage = $this->imageManager->canvas($this->image->width(), $this->image->height(), '#FFFFFF');
                //$jpgImage->insert($this->image);
                //$this->image = $jpgImage;
                $format = 'jpg';
            } else {
                $format = 'png';
            }
        } else {
            $format = 'jpg';
        }

        $source = trim((string)$this->image->encode($format, $quality));
        $path = $this->getModule()->get('storage')->getBucket($storageName)->upload($source, $format);

        $image = new Image();
        $image->source = $this->image;
        $image->bucket_id = ImageBucket::findOrCreateByName($storageName)->id;
        $image->path = $path;
        $image->quality = $quality;
        $image->width = $this->image->width();
        $image->height = $this->image->height();
        $image->parent_id = $parentId;
        $image->preview_hash = $previewHash;

        $image->save(false);

        return $image;
    }
}