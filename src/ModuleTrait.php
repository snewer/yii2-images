<?php

namespace snewer\images;

use Yii;

/**
 * Class ModuleTrait
 * @package snewer\images
 * @property Module $module
 */
trait ModuleTrait
{

    /**
     * @return Module
     */
    public function getModule()
    {
        return Yii::$app->getModule(Module::$_id, true);
    }

}