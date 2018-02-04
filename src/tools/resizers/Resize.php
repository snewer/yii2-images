<?php

namespace snewer\images\tools\resizers;

use yii\base\InvalidConfigException;

/**
 * Изменяет размер изображения без сохранения пропорций.
 *
 * Class Resize
 * @package snewer\images\tools\resizers
 */
class Resize extends Resizer
{

    public $width;

    public $height;

    public function getHash()
    {
        return 'resize:' . $this->width . ':' . $this->height;
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

    public function process()
    {
        $this->image->resize($this->width, $this->height);
    }

}