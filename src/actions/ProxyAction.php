<?php

namespace snewer\images\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;

class ProxyAction extends Action
{

    public function run()
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