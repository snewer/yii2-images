<?php

namespace snewer\images\tools;

use snewer\images\models\ImageUpload;

class Crop extends Tool
{

    public $x;

    public $y;

    public $width;

    public $height;

    public function process($image)
    {
        $imageWidth = $image->width();
        $imageHeight = $image->height();
        $x = ceil($this->x);
        $y = ceil($this->y);
        $width = ceil($this->width);
        $height = ceil($this->height);
        if ($x < 0) {
            $width = max($width + $x, ImageUpload::MIN_SIZE);
            $x = 0;
        }
        if ($y < 0) {
            $height = max($height + $y, ImageUpload::MIN_SIZE);
            $y = 0;
        }
        if ($x > $imageWidth - ImageUpload::MIN_SIZE) {
            $x = $imageWidth - ImageUpload::MIN_SIZE;
        }
        if ($y > $imageHeight - ImageUpload::MIN_SIZE) {
            $y = $imageHeight - ImageUpload::MIN_SIZE;
        }
        if ($x + $width > $imageWidth) {
            $width = $imageWidth - $x;
        }
        if ($y + $height > $imageHeight) {
            $height = $imageHeight - $y;
        }
        $image->crop($width, $height, $x, $y);
    }

}