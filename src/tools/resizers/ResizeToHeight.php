<?php

namespace snewer\images\tools\resizers;

class ResizeToHeight extends Resizer
{

    public $height;

    public function getHash()
    {
        return 'resize_to_height:' . $this->height;
    }

    public function process()
    {
        $this->image->heighten($this->height);
    }

}