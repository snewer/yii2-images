<?php

namespace snewer\images\traits;

use snewer\images\models\Image;
use snewer\images\ModuleTrait;
use snewer\images\tools\resizers\ResizeBackgroundColor;
use Yii;

trait GenerateImagePreviewsTrait
{
    use ModuleTrait;

    public function generateImagePreviews()
    {
        $map = $this->getModule()->previewsMap;
        foreach ($map as $className => $previews) {
            if ($className === self::class) {
                foreach ($previews as $relationName => $previewConfigurations) {
                    /* @var Image $image */
                    $image = $this->$relationName;
                    if ($image) {
                        foreach ($previewConfigurations as $configuration) {
                            if (!isset($configuration['class'])) {
                                $configuration['class'] = ResizeBackgroundColor::class;
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