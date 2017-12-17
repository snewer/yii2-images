<?php

namespace snewer\images\widgets;

use Yii;
use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use snewer\images\assets\WidgetAsset;
use snewer\images\models\ImageUpload;

class ImageUploadWidget extends InputWidget
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

    public $previews = [];

    public $emptyImage;

    public $cropperAsset = 'snewer\images\assets\CropperAsset';

    public $laddaAsset = 'snewer\images\assets\LaddaAsset';

    public $fontAwesomeAsset = 'snewer\images\assets\FontAwesomeAsset';

    public $magnificPopupAsset = 'snewer\images\assets\MagnificPopupAsset';

    protected $multiple = true;

    private function getInput()
    {
        $name = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name;
        $value = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;
        return Html::hiddenInput($name, $value, $this->options);
    }

    protected function registerAdditionalAssets()
    {
        if ($this->cropperAsset) {
            $this->view->registerAssetBundle($this->cropperAsset);
        }
        if ($this->laddaAsset) {
            $this->view->registerAssetBundle($this->laddaAsset);
        }
        if ($this->fontAwesomeAsset) {
            $this->view->registerAssetBundle($this->fontAwesomeAsset);
        }
        if ($this->magnificPopupAsset) {
            $this->view->registerAssetBundle($this->magnificPopupAsset);
        }
    }

    public function init()
    {
        parent::init();
        $this->previews[] = [400, 0];
        $this->registerAdditionalAssets();
        $widgetAsset = $this->view->registerAssetBundle(WidgetAsset::className());
        if ($this->emptyImage === null) {
            $this->emptyImage = Yii::$app->assetManager->getAssetUrl($widgetAsset, 'no-image.png');
        }
    }

    public function run()
    {
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
            'emptyImage' => $this->emptyImage
        ];
        $js = 'jQuery("#' . $this->options['id'] . '").ImagesWidget(' . Json::encode($options) . ');';
        $this->view->registerJs($js);
        return $this->getInput();
    }

}