<?php

namespace snewer\images\tools;

use yii\base\Object;
use snewer\images\ModuleTrait;

abstract class Tool extends Object
{

    use ModuleTrait;

    public function init()
    {
    }

    /**
     * @param \Intervention\Image\Image $image
     */
    abstract public function process($image);

}