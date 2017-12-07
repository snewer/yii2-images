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
        $this->_image->rotate(-$angle, $bgColor);
        // На некоторых изображениях обнаружились проблемы
        // с последовательным вызовом методов rotate и crop для драйвера Imagick.
        // Добавил issue: https://github.com/Intervention/image/issues/723
        // Это решает проблему:
        if ($this->_driver == 'Imagick') {
            $this->_image->getCore()->setImagePage($this->_image->width(), $this->_image->height(), 0, 0);
        }
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
            $width = max($width + $x, 0);
            $x = 0;
        }
        if ($y < 0) {
            $height = max($height + $y, 0);
            $y = 0;
        }
        if ($width > $imageWidth) {
            $width = $imageWidth;
        }
        if ($height > $imageHeight) {
            $height = $imageHeight;
        }
        $this->_image->crop($width, $height, $x, $y);
    }

    /**
     * @param string $base
     * @param array $away
     * @param int $tolerance
     * @param int $feather
     */
    public function trim($base = 'top-left', $away = ['top', 'bottom', 'left', 'right'], $tolerance = 0, $feather = 0)
    {
        $this->_image->trim($base, $away, $tolerance, $feather);
    }

    /**
     * @param $width
     * @param $height
     * @param int $minWidth
     * @param int $minHeight
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $aspectRatio
     * @param string $bgColor
     */
    public function resize($width = 0, $height = 0, $minWidth = self::MIN_SIZE, $minHeight = self::MIN_SIZE, $maxWidth = self::MAX_SIZE, $maxHeight = self::MAX_SIZE, $aspectRatio = 1, $bgColor = '#FFFFFF')
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
                $canvasAR = floatval($aspectRatio);
            } else {
                $w = max($this->_imageWidth, $minWidth);
                $h = max($this->_imageHeight, $minHeight);
                $canvasAR = $w / $h;
                unset($w, $h);
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

        // todo: также сделать следующий вариант изменения размера:
        // исходное изображение изменяется до размера $needleWidth х $needleHeight
        // искажается и затемняется. далее исходное изображение накладывается на это изображение
        $this->_image->resizeCanvas($canvasWidth, $canvasHeight, 'center', false, $bgColor);
    }

    /**
     * @param string $storageName - Название хранилища, в которое необходимо загрузить изображение
     * @param bool $supportAC
     * @param array $withPreviews
     * @param string $previewsStorageName
     * @return Image
     */
    public function upload($storageName, $supportAC = false, array $withPreviews = [], $previewsStorageName = null)
    {
        $image = new Image();
        // Была обнаружена проблема, когда md5 хэш файла не совпадал с хэшем файла,
        // который загружается в облачное хранилище по http. Trim решает проблему.
        $source = trim($this->_image->encode($supportAC ? 'png' : 'jpeg', 90));
        $path = $image->storage->upload($storageName, $source, $supportAC ? 'png' : 'jpg');
        $image->path = $path;
        $image->etag = md5($source);
        $image->parent_id = null;
        $image->quality = 90;
        $image->width = $this->_image->width();
        $image->height = $this->_image->height();
        $image->storage_id = $image->storage->getStorageIdByName($storageName);
        $image->source = $source;
        $image->save(false);
        // Добавляем preview изображения
        foreach ($withPreviews as $preview) {
            if (!isset($preview[0], $preview[1])) {
                throw new InvalidCallException('Структура массива неверная, элементы массива должны иметь вид [ширина, высота].');
            }
            $image->createPreview($preview[0], $preview[1], $previewsStorageName ?: $storageName);
        }
        return $image;
    }

}