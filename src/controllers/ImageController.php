<?php

namespace snewer\images\controllers;

use snewer\images\models\ImageUpload;
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

    public function actionUpload()
    {
        $source = Yii::$app->request->post('source');
        $options = Yii::$app->request->post('options');
        /* @var $storage \snewer\storage\StorageManager */
        $imageUpload = ImageUpload::load($source);

        if (isset($options['crop']['rotate'])) {
            $imageUpload->rotate($options['crop']['rotate'], $options['bgColor']);
        }
        if (isset($options['crop']['width'], $options['crop']['height'], $options['crop']['x'], $options['crop']['y'])) {
            $imageUpload->crop(
                $options['crop']['x'],
                $options['crop']['y'],
                $options['crop']['width'],
                $options['crop']['height']
            );
        }
        if ($options['trim'] === true || $options['trim'] == 'true') {
            $imageUpload->trim();
        }
        $imageUpload->resizeToBox(
            0,
            0,
            $options['minWidth'],
            $options['minHeight'],
            $options['maxWidth'],
            $options['maxHeight'],
            $options['aspectRatio'],
            $options['bgColor']
        );
        $image = $imageUpload->upload($this->module->imagesStoreStorageName, false, $this->module->imagesQuality);
        $image->save(false);
        $preview = $image->getOrCreatePreview(300, 300);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'success' => true,
            'image' => [
                'id' => $image->id,
                'url' => $image->url,
                'etag' => $image->etag,
                'width' => $image->width,
                'height' => $image->height
            ],
            'preview' => [
                'url' => $preview->url,
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
            $preview = $image->getOrCreatePreview(300, 300);
            if ($image) {
                return [
                    'success' => true,
                    'image' => [
                        'id' => $image->id,
                        'url' => $image->url,
                        'width' => $image->width,
                        'height' => $image->height
                    ],
                    'preview' => [
                        'url' => $preview->url,
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