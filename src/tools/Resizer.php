<?php

namespace snewer\images\tools;

use yii\base\Object;
use snewer\images\ModuleTrait;
use snewer\images\models\Image;

abstract class Resizer extends Object
{

    use ModuleTrait;

    /**
     * @param Image $image
     * @return Image
     */
    abstract public function process(Image $image);

}