<?php

namespace snewer\images\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;

/**
 * Class ImagesCollection
 * @package app\models
 *
 * @property $id
 * @property $type
 * @property $images
 */
class ImagesCollection extends ActiveRecord
{

    public static function tableName()
    {
        return 'images_collections';
    }

    public function behaviors()
    {
        return [
            ['class' => TimestampBehavior::className()],
            ['class' => BlameableBehavior::className()]
        ];
    }

    public function getImages()
    {
        // здесь пришлось отказаться от $this->many в силу того,
        // что нужно нужна сортировка по полю sort промежуточной таблицы
        $images = Image::find()
            ->select('[[images]].*')
            ->leftJoin('images_ic', '[[images_ic]].{{image_id}} = [[images]].{{id}}')
            ->where([
                '[[images_ic]].{{collection_id}}' => $this->id,
                '[[images_ic]].{{active}}' => 1,
            ])
            ->orderBy('[[images_ic]].{{sort}} ASC')
            ->all();

        // жадно извлекаем все проевью изображения и заполняем ими связь previews
        $imagesIds = ArrayHelper::getColumn($images, 'id', false);
        $previews = Image::find()->where(['parent_id' => $imagesIds])->all();
        if ($previews) {
            $previewsToPopulate = [];
            foreach ($previews as $preview) {
                $parent_id = (int) $preview['parent_id'];
                if (!isset($previewsToPopulate[$parent_id])) {
                    $previewsToPopulate[$parent_id] = [];
                }
                $previewsToPopulate[$parent_id][] = $preview;
            }
            foreach ($images as $image) {
                /* @var  $image Image */
                $parent_id = (int) $image->id;
                if (isset($previewsToPopulate[$parent_id])) {
                    $image->populateRelation('previews', $previewsToPopulate[$parent_id]);
                }
            }
        }

        return $images;
    }

    public function commit()
    {
        $this->commitOrResetRelations('commit');
    }

    public function reset()
    {
        $this->commitOrResetRelations('reset');
    }

    private function commitOrResetRelations($action)
    {
        $relations = ImagesCollection2Images::findAll(['collection_id' => $this->id]);
        $imagesIds = ArrayHelper::getColumn($relations, 'image_id');
        $images = Image::findAll(['id' => $imagesIds]);
        foreach ($images as $image) {
            if ($action == 'commit') {
                $image->commit();
            } else {
                $image->reset();
            }
        }
        foreach ($relations as $relation) {
            if ($action == 'commit') {
                $relation->commit();
            } else {
                $relation->reset();
            }
        }
    }

}