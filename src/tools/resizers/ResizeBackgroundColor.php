<?php

namespace snewer\images\tools\resizers;

use yii\base\InvalidConfigException;

/**
 * Изменяет размер изображения с сохранением пропорций.
 * Пустые области изображения закрашиваются фоновым цветом.
 * Можно указать минимальные и максимальные размеры изображения.
 *
 * Class ResizeToBox
 * @package snewer\images\tools\resizers
 */
class ResizeBackgroundColor extends Resizer
{

    public $width;

    public $height;

    public $bgColor = '#FFFFFF';

    /**
     * @inheritdoc
     */
    public function getHash()
    {
        $params = [
            $this->width,
            $this->height,
            $this->bgColor
        ];
        return 'rbc:' . implode(':', $params);
    }

    public function init()
    {
        if ($this->width <= 0) {
            throw new InvalidConfigException('Необходимо указать ширину.');
        }
        if ($this->height <= 0) {
            throw new InvalidConfigException('Необходимо указать высоту.');
        }
        // todo: проверка цвета.
        $this->width = ceil($this->width);
        $this->height = ceil($this->height);
    }

    /**
     * @inheritdoc
     */
    public function process()
    {
        $width = $this->image->width();
        $height = $this->image->height();
        $originalAR = $width / $height;
        $canvasAR = $this->width / $this->height;
        // если изображение не помещается в полотно, то уменьшаем его.
        if ($width > $this->width || $height > $this->height) {
            if ($originalAR >= $canvasAR) {
                $width = $this->width;
                $height = ceil($width / $originalAR);
            } else {
                $height = $this->height;
                $width = ceil($height * $originalAR);
            }
            $this->image->resize($width, $height);
        }
        $this->image->resizeCanvas($this->width, $this->height, 'center', false, $this->bgColor);
    }

}