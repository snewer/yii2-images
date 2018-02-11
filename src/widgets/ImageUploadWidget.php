<?php

namespace snewer\images\widgets;

use Yii;
use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\base\InvalidConfigException;
use snewer\images\assets\WidgetAsset;

class ImageUploadWidget extends InputWidget
{

    /**
     * Идентификатор модуля изображений.
     * @see \snewer\images\Module
     * @var string|null
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
     * Отношение сторон для выбора области пре обрезании изображения.
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

    private function getInput()
    {
        $name = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name;
        $value = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;
        return Html::hiddenInput($name, $value, $this->options);
    }

    protected function registerAssets()
    {
        if ($this->jqueryAsset) {
            $this->view->registerAssetBundle($this->jqueryAsset);
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
        if ($this->laddaAsset) {
            $this->view->registerAssetBundle($this->laddaAsset);
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
        if ($this->moduleId === null && ($this->getImageUrl === null || $this->uploadImageUrl === null || $this->proxyImageUrl === null)) {
            throw new InvalidConfigException('Необходимо задать {moduleId} или {getImageUrl}, {uploadImageUrl}, {proxyImageUrl} свойства.');
        }
        if ($this->getImageUrl === null) {
            $this->getImageUrl = Url::to(['/' . $this->moduleId . '/image/get']);
        }
        if ($this->uploadImageUrl === null) {
            $this->uploadImageUrl = Url::to(['/' . $this->moduleId . '/image/proxy']);
        }
        if ($this->proxyImageUrl === null) {
            $this->proxyImageUrl = Url::to(['/' . $this->moduleId . '/image/upload']);
        }
    }

    public function run()
    {
        $options = [
            'urls' => [
                'getImage' => $this->getImageUrl,
                'imageProxy' => $this->uploadImageUrl,
                'imageUpload' => $this->proxyImageUrl
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