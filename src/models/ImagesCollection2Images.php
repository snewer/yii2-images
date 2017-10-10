<?php

namespace snewer\images\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель для работы с промежуточной таблицой, связывающей изображения и коллекции
 *
 * @property $id
 * @property $collection_id
 * @property $image_id
 * @property $active - активность связи
 * @property $sort;
 */
class ImagesCollection2Images extends ActiveRecord
{

    public static function tableName()
    {
        return 'images_ic';
    }

    use ImagesCommitsAndResetsTrait;

}