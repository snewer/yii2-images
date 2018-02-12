<?php

namespace snewer\images\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;
use snewer\images\models\ImageUpload;
use snewer\images\ModuleTrait;

class UploadAction extends Action
{

    public $tools = [];

    use ModuleTrait;

    private function isTrue($value)
    {
        return $value === true || $value === 'true';
    }

    public function run()
    {
        $source = Yii::$app->request->post('source');
        $options = Yii::$app->request->post('options');
        $imageUpload = ImageUpload::load($source);
        if (isset($options['crop']['rotate'])) {
            $imageUpload->applyTool([
                'class' => 'snewer\images\tools\Rotate',
                'angle' => $options['crop']['rotate'],
                'bgColor' => $options['bgColor']
            ]);
        }
        if (isset($options['crop']['width'], $options['crop']['height'], $options['crop']['x'], $options['crop']['y'])) {
            $imageUpload->applyTool([
                'class' => 'snewer\images\tools\Crop',
                'x' => $options['crop']['x'],
                'y' => $options['crop']['y'],
                'width' => $options['crop']['width'],
                'height' => $options['crop']['height']
            ]);
        }

        if (is_array($this->tools)) {
            foreach ($this->tools as $tool) {
                $imageUpload->applyTool($tool);
            }
        }

        // Загрузка оригинала изображения
        $image = $imageUpload->upload(
            $this->getModule()->imagesStoreBucketName,
            isset($options['supportAC']) ? $this->isTrue($options['supportAC']) : false,
            $this->getModule()->imagesQuality
        );
        $image->save(false);
        $preview = $image->getPreviewBackgroundColor(300, 300);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'success' => true,
            'image' => [
                'id' => $image->id,
                'url' => $image->url,
                'integrity' => $image->integrity,
                'width' => $image->width,
                'height' => $image->height
            ],
            'preview' => [
                'url' => $preview->url,
                'integrity' => $image->integrity,
                'width' => $preview->width,
                'height' => $preview->height
            ]
        ];
    }

}