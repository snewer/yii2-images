<?php

namespace snewer\images\models;

use Yii;
use yii\db\ActiveRecord;
use yii\base\InvalidCallException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use snewer\images\ModuleTrait;

/**
 * Class Images
 *
 * From database:
 * @property $id
 * @property $preview_type
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
 * @property \snewer\storage\AbstractStorage $storage
 * @property string $storageName
 * @property string $source
 */
class Image extends ActiveRecord
{

    use ModuleTrait;

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

    private $_storageName;

    public function getStorageName()
    {
        if ($this->_storageName === null) {
            $storageModel = ImageStorage::findById($this->storage_id, true);
            $this->_storageName = $storageModel->name;
        }
        return $this->_storageName;
    }

    /**
     * @return \snewer\storage\AbstractStorage
     */
    public function getStorage()
    {
        $storage = Yii::$app->get('storage', true);
        return $storage->getStorage($this->storageName);
    }

    public function getUrl()
    {
        return $this->storage->getUrl($this->path);
    }

    public function getSource()
    {
        if ($this->_source === false) {
            $this->_source = $this->storage->getSource($this->path);
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
        $path = explode('.', $this->path);
        return array_pop($path) == 'png';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPreviews()
    {
        return $this
            ->hasMany(self::className(), ['parent_id' => 'id'])
            ->andWhere('deleted = 0')
            ->orderBy('quality DESC');
    }

    /**
     * Метод для получения существующего превью изображения
     * заданного размера и типа.
     * Вернется false, если првеью не существует.
     * @param $width
     * @param $height
     * @param $type
     * @return bool|Image
     */
    private function getPreview($width, $height, $type = ImageUpload::RESIZE_BOX)
    {
        foreach ($this->previews as $preview) {
            /* @var $preview self */
            if ($preview->width == $width && $preview->height == $height /*&& $preview->preview_type == $type*/) {
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
     * @param $type
     * @return Image
     */
    public function getOrCreatePreview($width = 0, $height = 0, $type = ImageUpload::RESIZE_BOX)
    {
        if ($width <= 0 && $height <= 0) {
            throw new InvalidCallException('Для превью необходимо указать ширину и/или высоту.');
        } elseif ($width <= 0) {
            $width = ceil($height * $this->width / $this->height);
        } elseif ($height <= 0) {
            $height = ceil($width * $this->height / $this->width);
        }
        $preview = $this->getPreview($width, $height, $type);
        if (!$preview) {
            // Создаем новую preview и добавляем ее в _related свойство ActiveRecord
            $preview = $this->createPreview($width, $height, $type);
            $relatedPreviews = $this->previews ?: [];
            $relatedPreviews[] = $preview;
            $this->populateRelation('previews', $relatedPreviews);
        }
        return $preview;
    }

    public function createPreview($width, $height, $type = ImageUpload::RESIZE_BOX)
    {
        $previewImageUploader = ImageUpload::load($this->source);
        $previewImageUploader->resize($width, $height, $type);
        $previewImage = $previewImageUploader->upload($this->getModule()->previewsStoreStorageName, $this->isSupportsAC(), $this->getModule()->previewsQuality);
        $previewImage->parent_id = $this->id;
        $previewImage->save(false);
        $relatedPreviews = $this->previews ?: [];
        $relatedPreviews[] = $previewImage;
        $this->populateRelation('previews', $relatedPreviews);
        return $previewImage;
    }

}