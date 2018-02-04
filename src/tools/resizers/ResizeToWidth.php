<?php

namespace snewer\images\tools\resizers;

class ResizeToWidth extends Resizer
{

    public $width;

    public function getHash()
    {
        return 'resize_to_width:' . $this->width;
    }

    public function process()
    {
        $this->image->widen($this->width);
    }

}