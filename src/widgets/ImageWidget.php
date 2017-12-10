<?php

namespace snewer\images\widgets;

use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use snewer\images\Asset;
use snewer\images\models\ImageUpload;

class ImageWidget extends InputWidget
{

    public $urls = [];

    public $trim = false;

    public $aspectRatio = 0;

    public $minWidth = ImageUpload::MIN_SIZE;

    public $minHeight = ImageUpload::MIN_SIZE;

    public $maxWidth = ImageUpload::MAX_SIZE;

    public $maxHeight = ImageUpload::MAX_SIZE;

    public $bgColor = '#FFFFFF';

    public $supportAC = false;

    public $previewWidth = 300;

    public $previewHeight = 300;

    public $previews = [];

    public $cropperAsset = 'snewer\images\CropperAsset';

    protected $multiple = true;

    private function getInput()
    {
        $name = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name;
        $value = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;
        return Html::hiddenInput($name, $value, $this->options);
    }

    public function init()
    {
        parent::init();
        $this->previews[] = [$this->previewWidth, $this->previewHeight];
    }

    public function run()
    {
        if ($this->cropperAsset) {
            $this->view->registerAssetBundle($this->cropperAsset);
        }
        $this->view->registerAssetBundle(Asset::className());

        $options = [
            'urls' => [
                'getImage' => Url::to(['images/image/get']),
                'imageProxy' => Url::to(['images/image/proxy']),
                'imageUpload' => Url::to(['images/image/upload'])
            ],
            'trim' => (bool)$this->trim,
            'aspectRatio' => (float)$this->aspectRatio,
            'minWidth' => (int)$this->minWidth,
            'minHeight' => (int)$this->minHeight,
            'maxWidth' => (int)$this->maxWidth,
            'maxHeight' => (int)$this->maxHeight,
            'supportAC' => (bool)$this->supportAC,
            'bgColor' => $this->bgColor,
        ];
        $js = 'jQuery("#' . $this->options['id'] . '").ImagesWidget(' . Json::encode($options) . ');';
        $this->view->registerJs($js);
        return $this->getInput();
    }

}