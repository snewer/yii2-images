<?php

namespace snewer\images\widgets;

use Yii;
use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use snewer\images\assets\WidgetAsset;

class ImageUploadWidget extends InputWidget
{

    /**
     * Идентификатор используемого модуля.
     * Используется для генерации ссылок по-умолчанию
     * в методе init().
     * @var string
     */
    public $moduleId = 'images';

    /**
     * Ссылка на действие получения изображения.
     * @see \snewer\images\actions\GetAction
     * @var string|null
     */
    public $getImageUrl;

    /**
     * Ссылка на действие загрузки изображения.
     * @see \snewer\images\actions\UploadAction
     * @var string|null
     */
    public $uploadImageUrl;

    /**
     * Ссылка на действие получения изображения по URL ссылке (прокси).
     * @see \snewer\images\actions\ProxyAction
     * @var string|null
     */
    public $proxyImageUrl;

    /**
     * Отношение сторон для выбора области при обрезании изображения.
     * Если установлено значение 0, то возможен произвольный выбор.
     * @var boolean
     */
    public $aspectRatio = 0;

    /**
     * Фон полотна изображения.
     * @var string
     */
    public $bgColor = '#FFFFFF';

    /**
     * Необходимоть поддержки альфа-канала изображения (прозрачности).
     * @var bool
     */
    public $supportAC = false;

    /**
     * Ссылка на изображение-заглушку.
     * @var string|null
     */
    public $emptyImage;

    /**
     * Jquery asset bundle
     * @var string
     */
    public $jqueryAsset = 'yii\web\JqueryAsset';

    /**
     * Bootstrap asset bundle
     * @var string
     */
    public $bootstrapAsset = 'yii\bootstrap\BootstrapAsset';

    /**
     * Bootstrap plugins asset bundle
     * @var string
     */
    public $bootstrapPluginAsset = 'yii\bootstrap\BootstrapPluginAsset';

    /**
     * Cropper asset bundle
     * @see https://github.com/fengyuanchen/cropper
     * @var string
     */
    public $cropperAsset = 'snewer\images\assets\CropperAsset';

    /**
     * Ladda asset bundle
     * @see https://github.com/hakimel/Ladda
     * @var string
     */
    public $laddaAsset = 'snewer\images\assets\LaddaAsset';

    /**
     * Font Awesome asset bundle
     * @see https://fontawesome.com
     * @var string
     */
    public $fontAwesomeAsset = 'snewer\images\assets\FontAwesomeAsset';

    /**
     * Magnific Popup asset bundle
     * @see http://dimsemenov.com/plugins/magnific-popup/
     * @var string
     */
    public $magnificPopupAsset = 'snewer\images\assets\MagnificPopupAsset';

    /**
     * @return string
     */
    private function getInput()
    {
        if (isset($this->options['name'])) {
            $name = $this->options['name'];
        } else {
            $name = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name;
        }
        if (isset($this->options['value'])) {
            $value = $this->options['value'];
        } else {
            $value = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;
        }
        return Html::hiddenInput($name, $value, $this->options);
    }

    protected function registerAssets()
    {
        if ($this->jqueryAsset) {
            $this->view->registerAssetBundle($this->jqueryAsset);
        }
        if ($this->laddaAsset) {
            $this->view->registerAssetBundle($this->laddaAsset);
        }
        if ($this->bootstrapAsset) {
            $this->view->registerAssetBundle($this->bootstrapAsset);
        }
        if ($this->bootstrapPluginAsset) {
            $this->view->registerAssetBundle($this->bootstrapPluginAsset);
        }
        if ($this->cropperAsset) {
            $this->view->registerAssetBundle($this->cropperAsset);
        }
        if ($this->fontAwesomeAsset) {
            $this->view->registerAssetBundle($this->fontAwesomeAsset);
        }
        if ($this->magnificPopupAsset) {
            $this->view->registerAssetBundle($this->magnificPopupAsset);
        }
        $widgetAsset = $this->view->registerAssetBundle(WidgetAsset::className());
        if ($this->emptyImage === null) {
            $this->emptyImage = Yii::$app->assetManager->getAssetUrl($widgetAsset, 'no-image.png');
        }
    }

    public function init()
    {
        parent::init();
        $this->registerAssets();
        if ($this->getImageUrl === null) {
            $this->getImageUrl = Url::to(['/' . $this->moduleId . '/image/get']);
        }
        if ($this->uploadImageUrl === null) {
            $this->uploadImageUrl = Url::to(['/' . $this->moduleId . '/image/upload']);
        }
        if ($this->proxyImageUrl === null) {
            $this->proxyImageUrl = Url::to(['/' . $this->moduleId . '/image/proxy']);
        }
    }

    public function run()
    {
        $options = [
            'urls' => [
                'getImage' => $this->getImageUrl,
                'imageProxy' => $this->proxyImageUrl,
                'imageUpload' => $this->uploadImageUrl
            ],
            'aspectRatio' => (float)$this->aspectRatio,
            'supportAC' => (bool)$this->supportAC,
            'bgColor' => $this->bgColor,
            'emptyImage' => $this->emptyImage
        ];
        $js = PHP_EOL . 'jQuery("#' . $this->options['id'] . '").ImageUploadWidget(' . Json::encode($options) . ');';
        $this->view->registerJs($js);
        return $this->getInput();
    }

}