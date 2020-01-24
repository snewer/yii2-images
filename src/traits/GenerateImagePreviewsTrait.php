<?php

namespace snewer\images\traits;

use snewer\images\models\Image;
use snewer\images\Module;
use snewer\images\tools\resizers\ResizeBestFit;
use Yii;

trait GenerateImagePreviewsTrait
{
    public function generateImagePreviews()
    {
        $map = Yii::$app->getModule(Module::$_id)->previewsMap;
        foreach ($map as $className => $previews) {
            if ($className === self::class) {
                foreach ($previews as $relationName => $previewConfigurations) {
                    /* @var Image $image */
                    $image = $this->$relationName;
                    if ($image) {
                        foreach ($previewConfigurations as $configuration) {
                            if (!isset($configuration['class'])) {
                                $configuration['class'] = ResizeBestFit::class;
                            }
                            $image->getPreviewByConfiguration($configuration);
                        }
                    }
                }
                break;
            }
        }
    }
}