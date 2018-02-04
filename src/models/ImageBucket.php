<?php

namespace snewer\images\models;

use yii\base\ErrorException;
use yii\db\ActiveRecord;

/**
 * Class ImageBucket
 * @package snewer\images\models
 * @property $id
 * @property $name
 */
class ImageBucket extends ActiveRecord
{

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName()
    {
        return '{{%images_buckets}}';
    }

    /**
     * @param integer $id
     * @param boolean $throwException
     * @throws ErrorException
     * @return array|null|self
     */
    public static function findById($id, $throwException = false)
    {
        $model = self::find()->where(['id' => $id])->limit(1)->one();
        if ($model === null && $throwException) {
            throw new ErrorException("Хранилище с идентификатором '{$id}' не найдено в базе данных.");
        }
        return $model;
    }

    /**
     * @param string $name
     * @param boolean $throwException
     * @throws ErrorException
     * @return array|null|self
     */
    public static function findByName($name, $throwException = false)
    {
        $model = self::find()->where(['name' => $name])->limit(1)->one();
        if ($model === null && $throwException) {
            throw new ErrorException("Хранилище с названием '{$name}' не найдено в базе данных.");
        }
        return $model;
    }

    /**
     * @param $name
     * @return array|null|self
     */
    public static function findOrCreateByName($name)
    {
        $model = self::findByName($name, false);
        if (!$model) {
            $model = new self;
            $model->name = $name;
            $model->save(false);
        }
        return $model;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(Image::className(), ['storage_id' => 'id']);
    }

}