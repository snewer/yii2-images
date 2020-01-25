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
 * @property $width
 * @property $height
 * @property $quality
 * @property $uploaded_at
 * @property $uploaded_by
 * @property $is_optimized
 *
 * @property $url
 * @property Image[] $previews
 * @property \snewer\storage\AbstractBucket $bucket
 * @property string $bucketName
 * @property $source
 */
class Image extends ActiveRecord
{

    use ModuleTrait;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'uploaded_at',
                'updatedAtAttribute' => false
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'uploaded_by',
                'updatedByAttribute' => false
            ]
        ];
    }

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_DELETE, function () {
            $this->getBucket()->delete($this->path);
            foreach ($this->previews as $preview) {
                $preview->getBucket()->delete($this->path);
            }
        });
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
        return $this->getModule()->get('storage')->getBucket($this->bucketName);
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
     * @return \yii\db\ActiveQuery
     */
    public function getPreviews()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
    }

    /**
     * Метод для получения существующего превью изображения по указанному хэшу.
     * Вернется null, если превью не существует.
     * @param $hash
     * @return null|Image
     */
    private function findPreview($hash)
    {
        foreach ($this->previews as $preview) {
            /* @var self $preview */
            if ($preview->preview_hash == $hash) {
                return $preview;
            }
        }
        return null;
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
        $preview = $uploader->upload($this->id, $resizer->getHash());

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
        if ($preview === null) {
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