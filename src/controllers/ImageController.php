<?php

namespace snewer\images\controllers;

use yii\web\Controller;

/**
 * Class ImageController
 * @package snewer\images\controllers
 * @property \snewer\images\Module $module
 */
class ImageController extends Controller
{

    public function actions()
    {
        return [
            'upload' => 'snewer\images\actions\UploadAction',
            'get' => 'snewer\images\actions\GetAction',
            'proxy' => 'snewer\images\actions\ProxyAction',
        ];
    }

}