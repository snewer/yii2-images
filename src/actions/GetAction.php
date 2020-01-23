<?php

namespace snewer\images\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;
use snewer\images\models\Image;

class GetAction extends Action
{

    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        if ($id) {
            $image = Image::findOne($id);
            $preview = $image->getPreviewBackgroundColor(300, 300);
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

}