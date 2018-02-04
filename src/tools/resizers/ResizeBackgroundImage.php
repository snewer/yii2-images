<?php

namespace snewer\images\tools\resizers;

use yii\base\InvalidConfigException;
use Intervention\Image\ImageManager;
use snewer\images\ModuleTrait;

/**
 * Изменяет размер изображения с сохранением пропорций.
 * Изображение не обрезается.
 * Пропорционально уменьшенное изображение накладывается на искаженное фоновое изображение.
 *
 * Class ResizeBackground
 * @package snewer\images\tools\resizers
 */
class ResizeBackgroundImage extends Resizer
{

    use ModuleTrait;

    public $width;

    public $height;

    public $greyscale = true;

    public $blur = 30;

    public $pixelate = 5;

    public $background;

    public function getHash()
    {
        $params = [
            $this->width,
            $this->height,
            $this->background === null ? 'self' : md5($this->background),
            $this->greyscale ? 1 : 0,
            $this->blur,
            $this->pixelate
        ];
        return 'rbi:' . implode(':', $params);
    }

    public function init()
    {
        if ($this->width <= 0) {
            throw new InvalidConfigException('Необходимо указать ширину.');
        }
        if ($this->height <= 0) {
            throw new InvalidConfigException('Необходимо указать высоту.');
        }
        $this->width = ceil($this->width);
        $this->height = ceil($this->height);
    }

    /**
     * @inheritdoc
     */
    public function process()
    {
        $original = clone $this->image;
        if ($this->background !== null) {
            $imageManager = new ImageManager(['driver' => $this->getModule()->driver]);
            $image = $imageManager->make($this->background);
        }
        $this->image->resize($this->width, $this->height);
        if ($this->greyscale) {
            $this->image->greyscale();
        }
        $this->image->pixelate($this->pixelate);
        $this->image->blur($this->blur);
        $width = $original->width();
        $height = $original->height();
        if ($width > $this->width || $height > $this->height) {
            $originalAR = $width / $height;
            if ($originalAR > $this->width / $this->height) {
                $width = $this->width;
                $height = $width / $originalAR;
            } else {
                $height = $this->height;
                $width = $height * $originalAR;
            }
            $original->resize($width, $height);
        }
        $this->image->insert($original, 'center');
    }

}