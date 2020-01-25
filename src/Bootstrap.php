<?php

namespace snewer\images;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    private function getModule(Application $app)
    {
        if ($app->hasModule('images') && ($module = $app->getModule('images')) instanceof Module) {
            Module::$_id = 'images';
            return $module;
        }

        foreach ($app->getModules() as $moduleName => $module) {
            if (is_array($module)) {
                if ($module['class'] === Module::class) {
                    $module = $app->getModule($moduleName);
                    Module::$_id = $moduleName;
                    return $module;
                }
            } elseif (($module = $app->getModule($moduleName)) instanceof Module) {
                Module::$_id = $moduleName;
                return $module;
            }
        }

        return null;
    }

    public function bootstrap($app)
    {
        $module = $this->getModule($app);
        if ($module !== null) {
            if ($app instanceof \yii\console\Application) {
                $module->controllerNamespace = 'snewer\images\commands';
            } else {
                // ...
            }
        }
    }
}