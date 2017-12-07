<?php

namespace snewer\images\models;

use Yii;
use yii\db\ActiveRecord;
use yii\base\InvalidCallException;
use Intervention\Image\ImageManager;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;

/**
 * Class Images
 *
 * From database:
 * @property $id
 * @property $parent_id
 * @property $storage_id
 * @property $path
 * @property $etag
 * @property $width
 * @property $height
 * @property $quality
 * @property $uploaded_at
 * @property $updated_at
 * @property $uploaded_by
 * @property $updated_by
 * @property $deleted
 *
 * via magic:
 * @property $url
 * @property $previews
 * @property \snewer\storage\StorageManager $storage
 * @property string $source
 */
class Image extends ActiveRecord
{

    private $_source = false;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'uploaded_at',
                'updatedAtAttribute' => 'updated_at'
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'uploaded_by',
                'updatedByAttribute' => 'updated_by'
            ]
        ];
    }

    public static function tableName()
    {
        return '{{%images}}';
    }

    /**
     * @return \snewer\storage\StorageManager
     */
    public function getStorage()
    {
        return Yii::$app->get('storage', true);
    }

    public function getUrl()
    {
        return $this->getStorage()->getStorageById($this->storage_id)->getUrl($this->path);
    }

    public function getSource()
    {
        if ($this->_source === false) {
            $this->_source = $this->getStorage()->getStorageById($this->storage_id)->getSource($this->path);
        }
        return $this->_source;
    }

    public function setSource($source)
    {
        $this->_source = $source;
    }


    /**
     * Поддерживает ли данное изображение альфа-канал.
     * То есть имеет ли изображение формат PNG.
     */
    public function isSupportsAC()
    {
        return array_pop(explode('.', $this->path)) == 'png';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPreviews()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'id'])->orderBy('quality DESC');
    }

    /**
     * Метод для получения существующего превью изображения
     * заданного размера, и, возможно, заданного качества.
     * Вернется false, если првеью не существует.
     * @param $width
     * @param $height
     * @return bool|Image
     */
    private function getPreview($width, $height)
    {
        foreach ($this->previews as $preview) {
            /* @var $preview self */
            if ($preview->width == $width && $preview->height == $height) {
                return $preview;
            }
        }
        return false;
    }

    /**
     * Метод для получения превью изображение заданного размера
     * Если превью не будет найдено, то оно будет создано.
     * @param $width
     * @param $height
     * @return Image
     */
    public function getOrCreatePreview($width = 0, $height = 0)
    {
        if ($width == 0 && $height == 0) {
            throw new InvalidCallException('Для превью необходимо указать ширину или высоту.');
        } elseif ($width == 0) {
            $width = ceil($height * $this->width / $this->height);
        } elseif ($height == 0) {
            $height = ceil($width * $this->height / $this->width);
        }
        $preview = $this->getPreview($width, $height);
        if (!$preview) {
            // создаем новую preview и добавляем ее в _related свойство ActiveRecord
            $preview = $this->createPreview($width, $height);
            $relatedPreviews = $this->previews ?: [];
            $relatedPreviews[] = $preview;
            $this->populateRelation('previews', $relatedPreviews);
        }
        return $preview;
    }

    public function createPreview($width, $height, $storageName)
    {
        $previewImageUploader = ImageUpload::load($this->source);
        // todo: resize
        $previewImage = $previewImageUploader->upload($storageName);
        $relatedPreviews = $this->previews ?: [];
        $relatedPreviews[] = $preview;
        $this->populateRelation('previews', $relatedPreviews);
    }

}