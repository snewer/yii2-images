<?php

namespace snewer\images\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use snewer\images\ModuleTrait;

/**
 * Class Images
 *
 * From database:
 * @property $id
 * @property $preview_hash
 * @property $parent_id
 * @property $bucket_id
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
 * @property \snewer\storage\AbstractBucket $bucket
 * @property string $bucketName
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

    private $_bucketName;

    public function getBucketName()
    {
        if ($this->_bucketName === null) {
            $bucketModel = ImageBucket::findById($this->bucket_id, true);
            $this->_bucketName = $bucketModel->name;
        }
        return $this->_bucketName;
    }

    /**
     * @return \snewer\storage\AbstractBucket
     */
    public function getBucket()
    {
        return $this->getModule()->getStorage()->getBucket($this->bucketName);
    }

    public function getUrl()
    {
        return $this->getBucket()->getUrl($this->path);
    }

    private $_source = false;

    public function getSource()
    {
        if ($this->_source === false) {
            $this->_source = $this->getBucket()->getSource($this->path);
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
        return array_pop(explode('.', $this->path)) == 'png';
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
     * @param $hash
     * @return false|Image
     */
    private function findPreview($hash)
    {
        foreach ($this->previews as $preview) {
            /* @var self $preview */
            if ($preview->preview_hash == $hash) {
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

        /* if ($width <= 0 && $height <= 0) {
             throw new InvalidCallException('Для превью необходимо указать ширину и/или высоту.');
         } elseif ($width <= 0) {
             $width = $height * $this->width / $this->height;
         } elseif ($height <= 0) {
             $height = $width * $this->height / $this->width;
         }*/
        if ($configuration === null) {
            $configuration = [
                'class' => 'snewer\images\tools\resizers\ResizeAndCrop',
            ];
        }

        if ($width > 0) {
            $configuration['width'] = $width;
        }

        if ($height > 0) {
            $configuration['height'] = $height;
        }

        $resizer = is_object($configuration) ? $configuration : Yii::createObject($configuration);
        $preview = $this->findPreview($resizer->getHash());
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
            $this->getModule()->previewsStoreBucketName,
            $this->isSupportsAC(),
            $this->getModule()->previewsQuality
        );
        $preview->parent_id = $this->id;
        $preview->preview_hash = $resizer->getHash();
        $preview->save(false);
        $relatedPreviews = $this->previews ?: [];
        $relatedPreviews[] = $preview;
        $this->populateRelation('previews', $relatedPreviews);
        return $preview;
    }

}