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
 * @property $uploaded_by
 *
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
                'updatedAtAttribute' => false
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'uploaded_by',
                'updatedByAttribute' => false
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
        $path = explode('.', $this->path);
        return array_pop($path) == 'png';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPreviews()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'id']);
    }

    /**
     * Метод для получения существующего превью изображения по указанному хэшу.
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
     * @param object|array $configuration - конфигурация или экземпляр наследника \snewer\images\tools\resizer\Resizer.
     * @return Image
     */
    private function createPreview($configuration)
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

    /**
     * Метод для получения превью изображения под заданной конфигурации.
     * Если превью не будет найдено, то оно будет создано.
     * @param $configuration
     * @return Image
     */
    public function getPreviewByConfiguration($configuration)
    {
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

    public function getPreviewCustom($width, $height)
    {
        return $this->getPreviewByConfiguration([
            'class' => 'snewer\images\tools\resizers\ResizeCustom',
            'width' => $width,
            'height' => $height
        ]);
    }

    public function getPreviewToWidth($width)
    {
        return $this->getPreviewByConfiguration([
            'class' => 'snewer\images\tools\resizers\ResizeToWidth',
            'width' => $width
        ]);
    }

    public function getPreviewToHeight($height)
    {
        return $this->getPreviewByConfiguration([
            'class' => 'snewer\images\tools\resizers\ResizeToHeight',
            'height' => $height
        ]);
    }

    public function getPreviewBackgroundColor($width, $height, $bgColor = '#FFFFFF')
    {
        return $this->getPreviewByConfiguration([
            'class' => 'snewer\images\tools\resizers\ResizeBackgroundColor',
            'width' => $width,
            'height' => $height,
            'bgColor' => $bgColor
        ]);
    }

    public function getPreviewBestFit($width, $height)
    {
        return $this->getPreviewByConfiguration([
            'class' => 'snewer\images\tools\resizers\ResizeBestFit',
            'width' => $width,
            'height' => $height
        ]);
    }

    public function getPreviewBackgroundImage($width, $height, $background = null, $greyscale = true, $blur = 30, $pixelate = 5)
    {
        return $this->getPreviewByConfiguration([
            'class' => 'snewer\images\tools\resizers\ResizeBackgroundImage',
            'width' => $width,
            'height' => $height,
            'background' => $background,
            'greyscale' => $greyscale,
            'blur' => $blur,
            'pixelate' => $pixelate
        ]);
    }

}