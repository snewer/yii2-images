<?php

namespace snewer\images\tools\resizers;

class Resize extends Resizer
{

    public $width;

    public $height;

    public function getHash()
    {
        return 'resize:' . $this->width . ':' . $this->height;
    }

    public function process()
    {
        $this->image->resize($this->width, $this->height);
    }

}