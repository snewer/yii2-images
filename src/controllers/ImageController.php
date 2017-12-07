<?php

namespace snewer\images\controllers;

use snewer\images\models\ImageUpload;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use snewer\images\models\Image;

class ImageController extends Controller
{

    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $source = Yii::$app->request->post('source');
        $options = Yii::$app->request->post('options');
        /* @var $module \snewer\images\Module */
        $module = Yii::$app->controller->module;
        /* @var $storage \snewer\storage\StorageManager */

        $imageUpload = ImageUpload::load($source);
        $image = $imageUpload->upload('images');

        /*$storage = Yii::$app->{$module->storageComponentName};
        $image = new $module->imagesStoreStorageName;
        $image->originalUploadStorageId = $storage->getStorageIdByName($module->imagesStoreStorageName);
        $image->previewUploadStorageId = $storage->getStorageIdByName($module->previewsStoreStorageName);
        if ($image->upload($source, $options, $module->previews)) {
            $preview = $image->getOrCreatePreview(300, 300);
            return [
                'success' => true,
                'original' => [
                    'id' => $image->id,
                    'url' => $image->url,
                    'etag' => $image->etag,
                    'width' => $image->width,
                    'height' => $image->height,
                    'title' => $image->title,
                    'description' => $image->description
                ],
                'preview' => [
                    'url' => $preview->url,
                    'width' => $preview->width,
                    'height' => $preview->height
                ]
            ];
        } else {
            return [
                'success' => false
            ];
        }*/
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
                    'original' => [
                        'id' => $image->id,
                        'url' => $image->url,
                        'title' => $image->title,
                        'description' => $image->description,
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
            $size = strlen($image);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $image);
            // todo: валидация изображениия
            finfo_close($finfo);
            if ($image && $size && $mimeType) {
                return [
                    'success' => true,
                    'base64' => 'data:' . $mimeType . ';base64,' . base64_encode($image),
                    'size' => $size,
                    'mime' => $mimeType
                ];
            }
        }
        return [
            'success' => false
        ];
    }

}