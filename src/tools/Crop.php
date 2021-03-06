<?php

namespace snewer\images\tools;

class Crop extends Tool
{

    public $x;

    public $y;

    public $width;

    public $height;

    public function process()
    {
        $imageWidth = $this->image->width();
        $imageHeight = $this->image->height();
        $x = ceil($this->x);
        $y = ceil($this->y);
        $width = ceil($this->width);
        $height = ceil($this->height);
        if ($x < 0) {
            $width = max($width + $x, self::MIN_SIZE);
            $x = 0;
        }
        if ($y < 0) {
            $height = max($height + $y, self::MIN_SIZE);
            $y = 0;
        }
        if ($x > $imageWidth - self::MIN_SIZE) {
            $x = $imageWidth - self::MIN_SIZE;
        }
        if ($y > $imageHeight - self::MIN_SIZE) {
            $y = $imageHeight - self::MIN_SIZE;
        }
        if ($x + $width > $imageWidth) {
            $width = $imageWidth - $x;
        }
        if ($y + $height > $imageHeight) {
            $height = $imageHeight - $y;
        }
        $this->image->crop($width, $height, $x, $y);
    }

}