<?php

namespace snewer\images\commands;

use snewer\images\models\Image;
use snewer\images\ModuleTrait;
use snewer\images\tools\resizers\ResizeBackgroundColor;
use yii\console\Controller;

class PreviewsController extends Controller
{
    use ModuleTrait;

    /**
     * Генерация preview изображений.
     */
    public function actionGenerate()
    {
        $map = $this->getModule()->previewsMap;
        foreach ($map as $className => $previews) {
            $models = $className::find()->with(array_keys($previews))->each();
            foreach ($models as $model) {
                foreach ($previews as $relationName => $previewConfigurations) {
                    /* @var Image $image */
                    $image = $model->$relationName;
                    if ($image) {
                        foreach ($previewConfigurations as $configuration) {
                            if (!isset($configuration['class'])) {
                                $configuration['class'] = ResizeBackgroundColor::class;
                            }
                            $image->getPreviewByConfiguration($configuration);
                        }
                    }
                }
            }
        }
    }

    public function actionRemoveAll()
    {
        $previews = Image::find()->where('parent_id IS NOT NULL')->each();
        foreach ($previews as $preview) {
            $preview->delete();
        }
    }
}