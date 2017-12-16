<?php

namespace snewer\images\models;

use Intervention\Image\ImageManager;
use yii\base\InvalidCallException;

class ImageUpload
{

    // минимальный размер стороны изображения
    const MIN_SIZE = 1;

    // максимальный размер стороны изображения
    const MAX_SIZE = 10000;

    const RESIZE_BOX = 1;

    /**
     * @var \Intervention\Image\Image
     */
    private $_image;

    /**
     * @var integer
     */
    private $_imageWidth;

    /**
     * @var integer
     */
    private $_imageHeight;

    /**
     * @var string
     */
    private $_driver;

    private function __construct($source, $driver)
    {
        $imageManager = new ImageManager(['driver' => $driver]);
        $this->_image = $imageManager->make($source);
        $this->_driver = $driver;
    }

    public static function load($source, $driver = 'GD')
    {
        return new self($source, $driver);
    }

    public function rotate($angle, $bgColor = '#FFFFFF')
    {
        $angle = floatval($angle);
        // Была обнаружена проблема при повороте изображения на 0 градусов:
        // вокруг него добавлялась белая рамка.
        // Поэтому не вызываем метод rotate когда поворавичвать изображение не нужно.
        if ($angle % 360 > 0) {
            $this->_image->rotate(-$angle, $bgColor);
        }
        // На некоторых изображениях обнаружились проблемы
        // с последовательным вызовом методов rotate и crop для драйвера Imagick.
        // Добавил issue: https://github.com/Intervention/image/issues/723
        // Это решает проблему:
        if ($this->_driver == 'Imagick') {
            $this->_image->getCore()->setImagePage($this->_image->width(), $this->_image->height(), 0, 0);
        }
        return $this;
    }

    public function crop($x, $y, $width, $height)
    {
        $imageWidth = $this->_image->width();
        $imageHeight = $this->_image->height();
        $x = ceil($x);
        $y = ceil($y);
        $width = ceil($width);
        $height = ceil($height);
        if ($x < 0) {
            $width = max($width + $x, self::MIN_SIZE);
            $x = 0;
        }
        if ($y < 0) {
            $height = max($height + $y, self::MIN_SIZE);
            $y = 0;
        }
        if ($x > $imageWidth - self::MIN_SIZE) {
            $x = $imageWidth - self::MIN_SIZE;
        }
        if ($y > $imageHeight - self::MIN_SIZE) {
            $y = $imageHeight - self::MIN_SIZE;
        }
        if ($x + $width > $imageWidth) {
            $width = $imageWidth - $x;
        }
        if ($y + $height > $imageHeight) {
            $height = $imageHeight - $y;
        }
        $this->_image->crop($width, $height, $x, $y);
        return $this;
    }

    /**
     * @param string $base
     * @param array $away
     * @param int $tolerance
     * @param int $feather
     * @return self
     */
    public function trim($base = 'top-left', $away = ['top', 'bottom', 'left', 'right'], $tolerance = 0, $feather = 0)
    {
        $this->_image->trim($base, $away, $tolerance, $feather);
        return $this;
    }

    /**
     * Изменяет размер изображения по следующему правилу:
     * 1) Изображение не должно быть меньше $minWidth x $minHeight
     * 2) Изображение не должно быть больше $maxWidth x $maxHeight
     * 3) Соотношение сторон должно быть равным $aspectRatio
     * 4) Изображение помещается в контейнер размером $width х $height
     * @param $width
     * @param $height
     * @param int $minWidth
     * @param int $minHeight
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $aspectRatio
     * @param string $bgColor
     * @return self
     */
    public function resizeToBox(
        $width = 0,
        $height = 0,
        $minWidth = self::MIN_SIZE,
        $minHeight = self::MIN_SIZE,
        $maxWidth = self::MAX_SIZE,
        $maxHeight = self::MAX_SIZE,
        $aspectRatio = 0,
        $bgColor = '#FFFFFF'
    )
    {
        // на данном этапе изображение, полученное после возможных crop и trim операций считаем оригинальным.
        $this->_imageWidth = $this->_image->width();
        $this->_imageHeight = $this->_image->height();
        $originalAR = $this->_imageWidth / $this->_imageHeight;
        if ($width > 0 && $height > 0) {
            // требуемые размеры полотна указаны явно
            $canvasWidth = ceil($width);
            $canvasHeight = ceil($height);
            $canvasAR = $canvasWidth / $canvasHeight;
        } else {
            if ($aspectRatio > 0) {
                // явно указано требуемое соотношение сторон полотна
                $canvasAR = $aspectRatio;
            } else {
                $canvasAR = max($this->_imageWidth, $minWidth) / max($this->_imageHeight, $minHeight);
            }
            // считаем размеры полотна.
            if ($originalAR >= $canvasAR) {
                $canvasWidth = $this->_imageWidth;
                $canvasHeight = ceil($canvasWidth / $canvasAR);
            } else {
                $canvasHeight = $this->_imageHeight;
                $canvasWidth = ceil($canvasHeight * $canvasAR);
            }
            // полотно шире допустимого значения
            if ($maxWidth > 0 && $maxWidth < $canvasWidth) {
                $canvasWidth = ceil($maxWidth);
                $canvasHeight = ceil($canvasWidth / $canvasAR);
            }
            // полотно выше допустимого значения
            if ($maxHeight > 0 && $maxHeight < $canvasHeight) {
                $canvasHeight = ceil($maxHeight);
                $canvasWidth = ceil($canvasHeight * $canvasAR);
            }
            // полотно уже допустимого значения
            if ($minWidth > 0 && $minWidth > $canvasWidth) {
                $canvasWidth = ceil($minWidth);
                $canvasHeight = ceil($canvasWidth / $canvasAR);
            }
            // полотно ниже допустимого значения
            if ($minHeight > 0 && $minHeight > $canvasHeight) {
                $canvasHeight = ceil($minHeight);
                $canvasWidth = ceil($canvasHeight * $canvasAR);
            }
        }

        // если изображение не помещается в полотно, то уменьшаем его.
        if ($this->_imageWidth > $canvasWidth || $this->_imageHeight > $canvasHeight) {
            if ($originalAR >= $canvasAR) {
                $this->_imageWidth = $canvasWidth;
                $this->_imageHeight = ceil($this->_imageWidth / $originalAR);
            } else {
                $this->_imageHeight = $canvasHeight;
                $this->_imageWidth = ceil($this->_imageHeight * $originalAR);
            }
            $this->_image->resize($this->_imageWidth, $this->_imageHeight);
        }

        $this->_image->resizeCanvas($canvasWidth, $canvasHeight, 'center', false, $bgColor);
        return $this;
    }

    public function resize($width, $height, $type)
    {
        return $this->resizeToBox($width, $height);
    }

    /**
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
        $source = trim($this->_image->encode($supportAC ? 'png' : 'jpeg', $quality));
        $path = $image->storage->upload($source, $supportAC ? 'png' : 'jpg');
        $image->path = $path;
        // https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity
        // todo: переименовать etag в integrity
        $image->etag = 'sha384-' . base64_encode(hash('sha384', $source, true));
        $image->parent_id = null;
        $image->quality = $quality;
        $image->width = $this->_image->width();
        $image->height = $this->_image->height();
        $image->source = $source;
        $image->deleted = 0;
        //$image->save(false);
        return $image;
    }

}