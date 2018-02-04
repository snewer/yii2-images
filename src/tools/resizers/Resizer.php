<?php

namespace snewer\images\tools\resizers;

use snewer\images\tools\Tool;

abstract class Resizer extends Tool
{

    abstract public function getHash();

}