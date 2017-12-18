<?php

namespace snewer\images\tools\resizers;

use snewer\images\models\ImageTypes;

class ResizeAndCrop extends Resizer
{

    public $width;

    public $height;


    public static function getType()
    {
        return ImageTypes::RESIZED_AND_CROPPED;
    }

    /**
     * @inheritdoc
     */
    public function process($image)
    {

        $width = $image->width();
        $height = $image->height();
        if ($width != $this->width || $height != $this->height) {
            $originalAR = $width / $height;
            if ($originalAR > $this->width / $this->height) {
                $height = $this->height;
                $width = $height * $originalAR;
            } else {
                $width = $this->width;
                $height = $width / $originalAR;
            }
            $image->resize($width, $height);
            $image->crop($this->width, $this->height, ceil(($width - $this->width) / 2), ceil(($height - $this->height) / 2));
        }

    }

}