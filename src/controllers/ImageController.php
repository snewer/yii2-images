<?php

namespace snewer\images\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use snewer\images\ModuleTrait;

/**
 * Class ImageController
 * @package snewer\images\controllers
 * @property \snewer\images\Module $module
 */
class ImageController extends Controller
{

    use ModuleTrait;

    public function behaviors()
    {
        return [
            'access' => $this->getModule()->controllerAccess
        ];
    }

    public function actions()
    {
        return [
            'upload' => 'snewer\images\actions\UploadAction',
            'get' => 'snewer\images\actions\GetAction',
            'proxy' => 'snewer\images\actions\ProxyAction',
        ];
    }

}