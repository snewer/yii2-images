<?php

namespace snewer\images\tools\resizers;

use Intervention\Image\ImageManager;
use snewer\images\ModuleTrait;

class ResizeBackground extends Resizer
{

    use ModuleTrait;

    public $width;

    public $height;

    public $greyscale = true;

    public $blur = 30;

    public $pixelate = 5;

    public $background;

    public function getHash()
    {
        $params = [
            $this->width,
            $this->height,
            $this->background === null ? 'self' : md5($this->background),
            $this->greyscale ? 1 : 0,
            $this->blur,
            $this->pixelate
        ];
        return 'resize_self_background:' . implode(':', $params);
    }

    /**
     * @inheritdoc
     */
    public function process()
    {
        $original = clone $this->image;
        if ($this->background !== null) {
            $imageManager = new ImageManager(['driver' => $this->getModule()->driver]);
            $image = $imageManager->make($this->background);
        }
        $this->image->resize($this->width, $this->height);
        if ($this->greyscale) {
            $this->image->greyscale();
        }
        $this->image->pixelate($this->pixelate);
        $this->image->blur($this->blur);
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
        $this->image->insert($original, 'center');
    }

}