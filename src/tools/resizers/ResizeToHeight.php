<?php

namespace snewer\images\tools\resizers;

/**
 * Пропорционально изменяет размер изображения до заданной высоты.
 *
 * Class ResizeToHeight
 * @package snewer\images\tools\resizers
 */
class ResizeToHeight extends Resizer
{

    public $height;

    public function getHash()
    {
        return 'rth:' . $this->height;
    }

    public function process()
    {
        $this->image->heighten($this->height);
    }

}