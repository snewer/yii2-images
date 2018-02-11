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

    private static $_buckets;

    private static $_bucketsNamesToIdMap;

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName()
    {
        return '{{%images_buckets}}';
    }

    private static function loadBuckets()
    {
        if (self::$_buckets === null) {
            self::$_buckets = self::find()->indexBy('id')->all();
            if (self::$_buckets) {
                self::$_bucketsNamesToIdMap = [];
                foreach (self::$_buckets as $id => $bucket) {
                    self::$_bucketsNamesToIdMap[$bucket->name] = $id;
                }
            } else {
                self::$_buckets = [];
                self::$_bucketsNamesToIdMap = [];
            }
        }
    }

    /**
     * @param integer $id
     * @param boolean $throwException
     * @throws ErrorException
     * @return array|null|self
     */
    public static function findById($id, $throwException = false)
    {
        self::loadBuckets();
        if (!isset(self::$_buckets[$id]) && $throwException) {
            throw new ErrorException("Хранилище с идентификатором '{$id}' не найдено в базе данных.");
        }
        return self::$_buckets[$id];
    }

    /**
     * @param string $name
     * @param boolean $throwException
     * @throws ErrorException
     * @return array|null|self
     */
    public static function findByName($name, $throwException = false)
    {
        self::loadBuckets();
        if (!isset(self::$_bucketsNamesToIdMap[$name]) && $throwException) {
            throw new ErrorException("Хранилище с названием '{$name}' не найдено в базе данных.");
        }
        return self::$_buckets[self::$_bucketsNamesToIdMap[$name]];
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
            self::$_buckets[$model->id] = $model;
            self::$_bucketsNamesToIdMap[$name] = $model->id;
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