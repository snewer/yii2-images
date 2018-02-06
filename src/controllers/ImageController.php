<?php

namespace snewer\images\controllers;

use snewer\images\models\ImageUpload;
use snewer\images\ModuleTrait;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use snewer\images\models\Image;

/**
 * Class ImageController
 * @package snewer\images\controllers
 * @property \snewer\images\Module $module
 */
class ImageController extends Controller
{

    use ModuleTrait;

    private function isTrue($value)
    {
        return $value === true || $value === 'true';
    }

    public function actionUpload()
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
        if (isset($options['trim']) && $this->isTrue($options['trim'])) {
            $imageUpload->applyTool('snewer\images\tools\Trim');
        }
        // Загрузка оригинала изображения
        $image = $imageUpload->upload(
            $this->getModule()->imagesStoreBucketName,
            isset($options['supportAC']) ? $this->isTrue($options['supportAC']) : false,
            $this->getModule()->imagesQuality
        );
        $image->save(false);
        $preview = $image->getPreviewBestFit(300, 300);
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

    public function actionGet()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        if ($id) {
            $image = Image::findOne($id);
            $preview = $image->getPreviewBestFit(300, 300);
            if ($image) {
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
        return [
            'success' => false
        ];
    }

    public function actionProxy()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $url = trim(Yii::$app->request->post('url'));
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $image = file_get_contents($url);
            if ($image) {
                $size = strlen($image);
                if ($size > 0) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_buffer($finfo, $image);
                    finfo_close($finfo);
                    if (strncasecmp('image/', $mimeType, 6) == 0) {
                        return [
                            'success' => true,
                            'base64' => 'data:' . $mimeType . ';base64,' . base64_encode($image),
                            'size' => $size,
                            'mime' => $mimeType
                        ];
                    } else {
                        $errorMessage = 'Ссылка не ведет на изображение.';
                    }
                } else {
                    $errorMessage = 'Не удалось загрузить изображение.';
                }
            } else {
                $errorMessage = 'Не удалось загрузить изображение.';
            }
        } else {
            $errorMessage = 'Передана не корректная ссылка.';
        }
        return [
            'success' => false,
            'message' => $errorMessage
        ];
    }

}