<?php

namespace snewer\images\tools\resizers;

/**
 * Пропорционально изменяет размер изображения до заданной ширины.
 *
 * Class ResizeToWidth
 * @package snewer\images\tools\resizers
 */
class ResizeToWidth extends Resizer
{

    public $width;

    public function getHash()
    {
        return 'rtw:' . $this->width;
    }

    public function process()
    {
        $this->image->widen($this->width);
    }

}