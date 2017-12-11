<?php

namespace snewer\images;

use Yii;

trait ModuleTrait
{

    /**
     * @return Module
     */
    public function getModule()
    {
        return Yii::$app->getModule('images');
    }

}