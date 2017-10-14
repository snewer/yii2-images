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
 * @property $title
 * @property $description
 * @property $copyrights
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
 */
class Image extends ActiveRecord
{

    // минимальный размер стороны изображения
    const MIN_SIZE = 1;
    // максимальный размер стороны изображения
    const MAX_SIZE = 10000;

    public $storageComponentName = 'storage';

    public $originalUploadStorageId = 1;
    public $previewUploadStorageId = 1;

    public $imageDriver = 'GD';
    public $canvasBgColor = '#FFFFFF';

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
        return 'images';
    }

    public function getUrl()
    {
        return Yii::$app->{$this->storageComponentName}->getStorageById($this->storage_id)->getUrl($this->path);
    }

    public function getSource()
    {
        if ($this->_source === false) {
            $this->_source = Yii::$app->{$this->storageComponentName}->getStorageById($this->storage_id)->getSource($this->path);
        }
        return $this->_source;
    }


    public function upload($source, array $options = [], array $withPreviews = [])
    {

        if (!$this->isNewRecord) {
            throw new InvalidCallException('Нельзя заменить существующее изображение');
        }

        if (!$this->validate()) {
            return false;
        }

        $defaultOptions = [
            'maxWidth' => static::MAX_SIZE,
            'maxHeight' => static::MAX_SIZE,
            'minWidth' => static::MIN_SIZE,
            'minHeight' => static::MIN_SIZE,
            'aspectRatio' => 0,
            'trim' => false,
            'crop' => false,
            'supportAC' => false,
            'width' => 0,
            'height' => 0
        ];

        $options = ArrayHelper::merge($defaultOptions, $options);

        $imageManager = new ImageManager(['driver' => $this->imageDriver]);
        $image = $imageManager->make($source);

        if (isset($options['crop']['rotate'])) {
            $image->rotate(-$options['crop']['rotate'], $this->canvasBgColor);
        }

        // Возникли проблемы с последовательным вызовом методов
        // rotate и crop для драйвера Imagick. Добавил issue:
        // https://github.com/Intervention/image/issues/723
        // Это решает проблему:
        $driverCore = $image->getCore();
        if ($driverCore instanceof \Imagick) {
            $driverCore->setImagePage($image->width(), $image->height(), 0, 0);
        }

        // crop image
        if (isset($options['crop']['width'], $options['crop']['height'], $options['crop']['x'], $options['crop']['y'])) {
            $imageWidth = $image->width();
            $imageHeight = $image->height();
            $x = ceil($options['crop']['x']);
            $y = ceil($options['crop']['y']);
            $width = ceil($options['crop']['width']);
            $height = ceil($options['crop']['height']);
            if ($x < 0) {
                $width = max($width + $x, 0);
                $x = 0;
            }
            if ($y < 0) {
                $height = max($height + $y, 0);
                $y = 0;
            }
            if ($width > $imageWidth) {
                $width = $imageWidth;
            }
            if ($height > $imageHeight) {
                $height = $imageHeight;
            }
            $image->crop($width, $height, $x, $y);
        }

        // trim image
        if ($options['trim'] === 'true' || $options['trim'] === true) {
            $image->trim();
        }

        // на данном этапе изображение, полученное после возможных crop и trim операций считаем оригинальным.
        $width = $image->width();
        $height = $image->height();
        $originalAR = $width / $height;

        if ($options['width'] > 0 && $options['height'] > 0) {
            // требуемые размеры полотна указаны явно
            $canvasWidth = ceil($options['width']);
            $canvasHeight = ceil($options['height']);
            $canvasAR = $canvasWidth / $canvasHeight;

        } else {
            if ($options['aspectRatio'] > 0) {
                // явно указано требуемое соотношение сторон полотна
                $canvasAR = floatval($options['aspectRatio']);
            } else {
                $w = max($width, $options['minWidth']);
                $h = max($height, $options['minHeight']);
                $canvasAR = $w / $h;
                unset($w, $h);
            }
            // считаем размеры полотна.
            if ($originalAR >= $canvasAR) {
                $canvasWidth = $width;
                $canvasHeight = ceil($canvasWidth / $canvasAR);
            } else {
                $canvasHeight = $height;
                $canvasWidth = ceil($canvasHeight * $canvasAR);
            }
            // полотно шире допустимого значения
            if ($options['maxWidth'] > 0 && $options['maxWidth'] < $canvasWidth) {
                $canvasWidth = ceil($options['maxWidth']);
                $canvasHeight = ceil($canvasWidth / $canvasAR);
            }
            // полотно выше допустимого значения
            if ($options['maxHeight'] > 0 && $options['maxHeight'] < $canvasHeight) {
                $canvasHeight = ceil($options['maxHeight']);
                $canvasWidth = ceil($canvasHeight * $canvasAR);
            }
            // полотно уже допустимого значения
            if ($options['minWidth'] > 0 && $options['minWidth'] > $canvasWidth) {
                $canvasWidth = ceil($options['minWidth']);
                $canvasHeight = ceil($canvasWidth / $canvasAR);
            }
            // полотно ниже допустимого значения
            if ($options['minHeight'] > 0 && $options['minHeight'] > $canvasHeight) {
                $canvasHeight = ceil($options['minHeight']);
                $canvasWidth = ceil($canvasHeight * $canvasAR);
            }
        }

        // если изображение не умещается в полотно, то уменьшаем его
        if ($width > $canvasWidth || $height > $canvasHeight) {
            if ($originalAR >= $canvasAR) {
                $width = $canvasWidth;
                $height = ceil($width / $originalAR);
            } else {
                $height = $canvasHeight;
                $width = ceil($height * $originalAR);
            }
            $image->resize($width, $height);
        }


        // todo: также сделать следующий вариант изменения размера:
        // исходное изображение изменяется до размера $needleWidth х $needleHeight
        // искажается и затемняется. далее исходное изображение накладывается на это изображение
        $image->resizeCanvas($canvasWidth, $canvasHeight, 'center', false, $this->canvasBgColor);

        // Обязательно использовать trim() !!!
        // некоторые графиеские драйвера вставляют переносы строк,
        // которые пропадают при передаче файла в некоторые хранилища (selectel)
        // и md5 хэши после этого не совпадают!!!
        $resultSource = trim($image->encode(
            $options['supportAC'] === "true" || $options['supportAC'] === true ? 'png' : 'jpeg',
            90
        ));
        $this->_source = $resultSource;

        $storageManager = Yii::$app->{$this->storageComponentName};
        /* @var $storage \snewer\storage\AbstractStorage */
        $storage = $storageManager->getStorageById($this->originalUploadStorageId);
        $path = $storage->upload($resultSource, $options['supportAC'] === "true" || $options['supportAC'] === true ? 'png' : 'jpg');

        $this->parent_id = null;
        $this->quality = 90;
        $this->width = $width;
        $this->height = $height;
        $this->path = $path;
        $this->etag = md5($resultSource);
        $this->storage_id = $this->originalUploadStorageId;
        $success = $this->save(false);

        $previews = [];
        $addedPreviews = [];
        foreach ($withPreviews as $preview) {
            if (
                isset($preview[0], $preview[1]) &&
                min($preview[0], $preview[1]) > 0 &&
                !in_array($preview[0] . 'x' . $preview[1], $addedPreviews)
            ) {
                $previews[] = $this->createPreview($preview[0], $preview[1]);
                $addedPreviews[] = $preview[0] . 'x' . $preview[1];
            }
        }
        // превью 300х300 обязательно нужно (для редактора изображений)
        if (!in_array('300x300', $addedPreviews)) {
            $previews[] = $this->createPreview(300, 300);
        }
        $this->populateRelation('previews', $previews);

        return $success;
    }

    private function createPreview($needleWidth, $needleHeight)
    {
        if ($this->isNewRecord) {
            throw new InvalidCallException('Нельзя создать preview для не сохраненного изображения');
        }

        if ($this->parent_id > 0) {
            throw new InvalidCallException('Нельзя создать preview для preview');
        }

        $imageManager = new ImageManager(['driver' => $this->imageDriver]);
        $image = $imageManager->make($this->getSource());
        $image->trim();
        $width = $image->width();
        $height = $image->height();
        if ($width > $needleWidth || $height > $needleHeight) {
            $originalAR = $width / $height;
            if ($originalAR > $needleWidth / $needleHeight) {
                $image->resize($needleWidth, ceil($needleWidth / $originalAR));
            } else {
                $image->resize(ceil($needleHeight * $originalAR), $needleHeight);
            }
        }
        $image->resizeCanvas($needleWidth, $needleHeight, 'center', false, $this->canvasBgColor);
        $preview = new self;
        // Обязательно использовать trim() !!!
        // некоторые графиеские драйвера вставляют переносы строк,
        // которые пропадают при передаче файла в некоторые хранилища (selectel)
        // и md5 хэши после этого не совпадают!!!
        $resultSource = trim($image->encode('jpeg', $preview->quality));
        $storageManager = Yii::$app->{$this->storageComponentName};
        /* @var $storage \snewer\storage\AbstractStorage */
        $storage = $storageManager->getStorageById($this->previewUploadStorageId);
        $path = $storage->upload($resultSource, 'jpg');
        $preview->parent_id = $this->id;
        $preview->quality = 90;
        $preview->width = $needleWidth;
        $preview->height = $needleHeight;
        $preview->path = $path;
        $preview->etag = md5($resultSource);
        $preview->storage_id = $this->previewUploadStorageId;
        $preview->save(false);
        return $preview;
    }

    public function getPreviews()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'id'])->orderBy('quality DESC');
    }

    /**
     * Метод для получения существующего превью изображения
     * заданного размера, и возможно, заданного качества.
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
            throw new InvalidCallException('Для превью необходимо указать ширину или высоту');
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

    use ImagesCommitsAndResetsTrait;

}