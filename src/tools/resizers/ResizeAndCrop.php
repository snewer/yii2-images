<?php

namespace snewer\images\tools\resizers;

class ResizeAndCrop extends Resizer
{

    public $width;

    public $height;

    public function getHash()
    {
        $params = [
            $this->width,
            $this->height
        ];
        return 'resize_and_crop:' . implode(':', $params);
    }

    /**
     * @inheritdoc
     */
    public function process()
    {

        $width = $this->image->width();
        $height = $this->image->height();
        if ($width != $this->width || $height != $this->height) {
            $ar = $width / $height;
            if ($ar > $this->width / $this->height) {
                $height = $this->height;
                $width = $height * $ar;
            } else {
                $width = $this->width;
                $height = $width / $ar;
            }
            $this->image->resize($width, $height);
            $this->image->crop($this->width, $this->height, ceil(($width - $this->width) / 2), ceil(($height - $this->height) / 2));
        }

    }

}