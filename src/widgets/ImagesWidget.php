<?php

namespace snewer\images\widgets;

use Yii;
use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\base\InvalidConfigException;
use snewer\images\Asset;

class ImagesWidget extends InputWidget {

    public $urls = [];

    public $trim = false;
    public $aspectRatio = 0;
    public $minWidth = 0;
    public $minHeight = 0;
    public $maxWidth = 0;
    public $maxHeight = 0;
    public $supportAC = false;

    protected $multiple = true;

    private function getInput(){
        $name = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name;
        $value = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;
        return Html::hiddenInput($name, $value, $this->options);
    }

    public function run(){

        Asset::register($this->view);

        $options = [
            'urls' => [
                'addImageToCollection' => Url::to(['images/collection/add-image']),
                'createImagesCollection' => Url::to(['images/collection/create']),
                'deleteImageFromCollection' => Url::to(['images/collection/delete']),
                'getImage' => Url::to(['images/image/get']),
                'getImagesCollection' => Url::to(['images/collection/get']),
                'imageProxy' => Url::to(['images/image/proxy']),
                'imageUpload' => Url::to(['images/image/upload']),
                'sortImagesCollection' => Url::to(['images/collection/sort']),
            ],
            'trim' => (bool) $this->trim,
            'aspectRatio' => (float) $this->aspectRatio,
            'minWidth' => (int) $this->minWidth,
            'minHeight' => (int) $this->minHeight,
            'maxWidth' => (int) $this->maxWidth,
            'maxHeight' => (int) $this->maxHeight,
            'supportAC' => (bool) $this->supportAC,
        ];

        /*$hashData = json_encode([
            'trim' => (string) $options['trim'],
            'aspectRatio' => (string) $options['aspectRatio'],
            'minWidth' => (string) $options['minWidth'],
            'minHeight' => (string) $options['minHeight'],
            'maxWidth' => (string) $options['maxWidth'],
            'maxHeight' => (string) $options['maxHeight'],
            'supportAC' => (string) $options['supportAC']
        ]);
        $macHash = Yii::$app->security->macHash;
        $test = @hash_hmac($macHash, '', '', false);
        if (!$test) {
            throw new InvalidConfigException('Failed to generate HMAC with hash algorithm: ' . $macHash);
        }
        $hashKey = hash_hmac(Yii::$app->security->macHash, $hashData, Yii::$app->request->cookieValidationKey);
        $options['hash'] = $hashKey;*/

        $js = 'jQuery("#' . $this->options['id'] . '").ImagesWidget(';
        $js .= Json::encode($options);
        $js .= ',';
        $js .= $this->multiple ? 'true' : 'false';
        $js .= ');';
        $this->view->registerJs($js);
        return $this->getInput();
    }

}