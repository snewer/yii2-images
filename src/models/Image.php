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
 * @property $type
 * @property $parent_id
 * @property $storage_id
 * @property $path
 * @property $integrity
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
        return $this->getStorage()->getUrl($this->path);
    }

    private $_source = false;

    public function getSource()
    {
        if ($this->_source === false) {
            $this->_source = $this->getStorage()->getSource($this->path);
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
     * @return boolean
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
     * Метод для получения существующего превью изображения заданного размера и типа.
     * Вернется false, если првеью не существует.
     * @param $width
     * @param $height
     * @param $type
     * @return false|Image
     */
    private function findPreview($width, $height, $type)
    {
        foreach ($this->previews as $preview) {
            /* @var $preview self */
            if ($preview->width == $width && $preview->height == $height && $preview->type == $type) {
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
     * @param $configuration
     * @return Image
     */
    public function getOrCreatePreview($width = 0, $height = 0, $configuration = null)
    {
        if ($width <= 0 && $height <= 0) {
            throw new InvalidCallException('Для превью необходимо указать ширину и/или высоту.');
        } elseif ($width <= 0) {
            $width = $height * $this->width / $this->height;
        } elseif ($height <= 0) {
            $height = $width * $this->height / $this->width;
        }
        if ($configuration === null) {
            $configuration = [
                'class' => 'snewer\images\tools\resizers\ResizeToBox',
                'width' => $width,
                'height' => $height
            ];
        } elseif (is_array($configuration)) {
            $configuration['width'] = $width;
            $configuration['height'] = $height;
        } elseif (is_object($configuration)) {
            $configuration->width = $width;
            $configuration->height = $height;
        }
        $resizer = is_object($configuration) ? $configuration : Yii::createObject($configuration);
        $preview = $this->findPreview($width, $height, $resizer->getType());
        if (!$preview) {
            $preview = $this->createPreview($resizer);
            $relatedPreviews = $this->previews ?: [];
            $relatedPreviews[] = $preview;
            $this->populateRelation('previews', $relatedPreviews);
        }
        return $preview;
    }

    /**
     * @param object|array $configuration - конфигурация или экземпляр наследника \snewer\images\tools\resizer\Resizer.
     * @return Image
     */
    public function createPreview($configuration)
    {
        $resizer = is_object($configuration) ? $configuration : Yii::createObject($configuration);
        $uploader = ImageUpload::extend($this);
        $uploader->applyTool($resizer);
        $preview = $uploader->upload(
            $this->getModule()->previewsStoreStorageName,
            $this->isSupportsAC(),
            $this->getModule()->previewsQuality
        );
        $preview->parent_id = $this->id;
        $preview->type = $resizer->getType();
        $preview->save(false);
        $relatedPreviews = $this->previews ?: [];
        $relatedPreviews[] = $preview;
        $this->populateRelation('previews', $relatedPreviews);
        return $preview;
    }

}