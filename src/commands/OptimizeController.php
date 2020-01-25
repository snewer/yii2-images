<?php

namespace snewer\images\commands;

use Yii;
use snewer\images\models\Image;
use yii\console\Controller;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class OptimizeController extends Controller
{
    public function actionIndex($onlyPreviews = true)
    {
        $tmpDir = Yii::$app->getRuntimePath();
        $optimizer = OptimizerChainFactory::create();

        $query = Image::find();
        $query->where('is_optimized = false');
        if ($onlyPreviews) {
            $query->andWhere('parent_id IS NOT NULL');
        }

        foreach ($query->each() as $image) {
            /* @var Image $image */
            $tmpFile = $tmpDir . '/' . pathinfo($image->path, PATHINFO_BASENAME);
            file_put_contents($tmpFile, $image->source);
            $optimizer->optimize($tmpFile);
            $bucket = $image->getBucket();
            $bucket->replace($image->path, file_get_contents($tmpFile));
            $image->is_optimized = true;
            $image->save(false);
        }
    }
}