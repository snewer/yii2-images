<?php

namespace snewer\images\tools\resizers;

use snewer\images\models\ImageTypes;

class ResizeToBox extends Resizer
{

    /**
     * Минимальный размер стороны изображения.
     */
    const MIN_SIZE = 1;

    /**
     * Максимальный размер стороны изображения.
     */
    const MAX_SIZE = 10000;

    public $width = 0;

    public $height = 0;

    public $minWidth = self::MIN_SIZE;

    public $minHeight = self::MIN_SIZE;

    public $maxWidth = self::MAX_SIZE;

    public $maxHeight = self::MAX_SIZE;

    public $aspectRatio = 0;

    public $bgColor = '#FFFFFF';

    /**
     * @inheritdoc
     */
    public static function getType()
    {
        return ImageTypes::RESIZED_TO_BOX;
    }

    /**
     * @inheritdoc
     */
    public function process($image)
    {
        $width = $image->width();
        $height = $image->height();
        $originalAR = $width / $height;
        if ($this->width > 0 && $this->height > 0) {
            // требуемые размеры полотна указаны явно
            $canvasWidth = ceil($this->width);
            $canvasHeight = ceil($this->height);
            $canvasAR = $canvasWidth / $canvasHeight;
        } else {
            if ($this->aspectRatio > 0) {
                // явно указано требуемое соотношение сторон полотна
                $canvasAR = $this->aspectRatio;
            } else {
                $canvasAR = max($width, $this->minWidth) / max($height, $this->minHeight);
            }
            // считаем размеры полотна.
            if ($originalAR >= $canvasAR) {
                $canvasWidth = $width;
                $canvasHeight = ceil($canvasWidth / $canvasAR);
            } else {
                $canvasHeight = $height;
                $canvasWidth = ceil($canvasHeight * $canvasAR);
            }
            // полотно шире допустимого значения
            if ($this->maxWidth > 0 && $this->maxWidth < $canvasWidth) {
                $canvasWidth = ceil($this->maxWidth);
                $canvasHeight = ceil($canvasWidth / $canvasAR);
            }
            // полотно выше допустимого значения
            if ($this->maxHeight > 0 && $this->maxHeight < $canvasHeight) {
                $canvasHeight = ceil($this->maxHeight);
                $canvasWidth = ceil($canvasHeight * $canvasAR);
            }
            // полотно уже допустимого значения
            if ($this->minWidth > 0 && $this->minWidth > $canvasWidth) {
                $canvasWidth = ceil($this->minWidth);
                $canvasHeight = ceil($canvasWidth / $canvasAR);
            }
            // полотно ниже допустимого значения
            if ($this->minHeight > 0 && $this->minHeight > $canvasHeight) {
                $canvasHeight = ceil($this->minHeight);
                $canvasWidth = ceil($canvasHeight * $canvasAR);
            }
        }
        // если изображение не помещается в полотно, то уменьшаем его.
        if ($width > $canvasWidth || $height > $canvasHeight) {
            if ($originalAR >= $canvasAR) {
                $width = $canvasWidth;
                $height = ceil($width / $originalAR);
            } else {
                $height = $canvasHeight;
                $width = ceil($height * $originalAR);
            }
            $image->resize($width, $height);
        }
        $image->resizeCanvas($canvasWidth, $canvasHeight, 'center', false, $this->bgColor);
    }

}