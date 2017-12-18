<?php

namespace snewer\images\tools\resizers;

use snewer\images\models\ImageTypes;

class ResizeSelfBackground extends Resizer
{

    public $width;

    public $height;

    public $greyscale = true;

    public $blur = 30;

    public $pixelate = 5;


    public static function getType()
    {
        return ImageTypes::RESIZED_SELF_BACKGROUND;
    }

    /**
     * @inheritdoc
     */
    public function process($image)
    {
        $original = clone $image;
        $image->resize($this->width, $this->height);
        if ($this->greyscale) {
            $image->greyscale();
        }
        $image->pixelate($this->pixelate);
        $image->blur($this->blur);
        $width = $original->width();
        $height = $original->height();
        if ($width > $this->width || $height > $this->height) {
            $originalAR = $width / $height;
            if ($originalAR > $this->width / $this->height) {
                $width = $this->width;
                $height = $width / $originalAR;
            } else {
                $height = $this->height;
                $width = $height * $originalAR;

            }
            $original->resize($width, $height);
        }
        $image->insert($original, 'center');
    }

}