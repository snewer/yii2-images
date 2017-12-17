<?php

namespace snewer\images\helpers;

use Yii;
use yii\web\NotFoundHttpException;
use snewer\images\models\Image;

class ResizeImage
{

    /**
     * @param Image $imageModel
     * @param array $configuration
     * @return Image
     */
    public static function getByImageModel(Image $imageModel, array $configuration)
    {
        /* @var \snewer\images\tools\Resizer $resizer */
        $resizer = Yii::createObject($configuration);
        return $resizer->process($imageModel);
    }

    /**
     * @param $imageId
     * @param $configuration
     * @return Image
     * @throws NotFoundHttpException
     */
    public static function getById($imageId, $configuration)
    {
        /* @var Image $imageModel */
        $imageModel = Image::find()->where(['id' => $imageId])->limit(1)->one();
        if ($imageModel === null) {
            throw new NotFoundHttpException('Изображение с идентификатором \'' . $imageId . '\' не найдено.');
        }
        return self::getByImageModel($imageModel, $configuration);
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param array $configuration
     * @return Image
     * @throws NotFoundHttpException
     */
    public static function getByModel($model, $attribute, $configuration)
    {
        if (is_numeric($model->$attribute)) {
            return self::getById($model->$attribute, $configuration);
        } else {
            return self::getByImageModel($model->$attribute, $configuration);
        }
    }

}