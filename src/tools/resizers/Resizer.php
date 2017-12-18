<?php

namespace snewer\images\tools\resizers;

use snewer\images\tools\Tool;

abstract class Resizer extends Tool
{

    public $width;

    public $height;

    abstract public static function getType();

}