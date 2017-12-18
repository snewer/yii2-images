<?php

namespace snewer\images\tools;

class Trim extends Tool
{

    public $base = 'top-left';

    public $away = ['top', 'bottom', 'left', 'right'];

    public $tolerance = 0;

    public $feather = 0;

    public function process($image)
    {
        $image->trim($this->base, $this->away, $this->tolerance, $this->feather);
    }

}