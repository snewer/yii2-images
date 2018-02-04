<?php

namespace snewer\images\tools\resizers;

use yii\base\InvalidConfigException;

/**
 * Изменяет размер изображения с сохранением пропорций.
 * Участки изображения, не вместившиеся в область обрезаются.
 *
 * Class ResizeBestFit
 * @package snewer\images\tools\resizers
 */
class ResizeBestFit extends Resizer
{

    public $width;

    public $height;

    public function getHash()
    {
        $params = [
            $this->width,
            $this->height
        ];
        return 'rbf:' . implode(':', $params);
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
        $this->image->fit($this->width, $this->height);
    }

}