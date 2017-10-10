<?php

namespace snewer\images\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use snewer\images\models\ImagesCollection;
use snewer\images\models\ImagesCollection2Images;

class CollectionController extends Controller
{

    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $collection = new ImagesCollection();
        $collection->type = 0;
        if ($collection->save()) {
            return [
                'success' => true,
                'id' => $collection->id
            ];
        } else {
            return [
                'success' => false
            ];
        }
    }

    public function actionAddImage()
    {
        $imageId = Yii::$app->request->post('image_id');
        $collection_id = Yii::$app->request->post('collection_id');
        $sort = Yii::$app->request->post('sort');
        $relationModel = new ImagesCollection2Images();
        $relationModel->image_id = $imageId;
        $relationModel->collection_id = $collection_id;
        $relationModel->sort = $sort;
        // Делаем связь неактивной, что бы во время редактирования, изображение не стало
        // доступно пользователям/
        $relationModel->active = 0;
        $relationModel->save();
        ImagesCollection2Images::onCommit($relationModel->id, ['active' => 1]);
        ImagesCollection2Images::onReset($relationModel->id, [], true);
    }

    public function actionDelete()
    {
        $imageId = Yii::$app->request->post('image_id');
        $collectionId = Yii::$app->request->post('collection_id');
        /* @var $relation ImagesCollection2Images */
        $relation = ImagesCollection2Images::find()
            ->where(['image_id' => $imageId, 'collection_id' => $collectionId])
            ->limit(1)
            ->one();
        ImagesCollection2Images::onCommit($relation->id, null, true);
    }

    public function actionGet()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        if ($id) {
            $collection = ImagesCollection::findOne($id);
            if ($collection) {
                $images = $collection->images;
                $result = [
                    'success' => true,
                    'images' => []
                ];
                if ($images) {
                    foreach ($images as $image) {
                        /* @var $image \snewer\images\models\Image */
                        $preview = $image->getOrCreatePreview(300, 300);
                        $result['images'][] = [
                            'original' => [
                                'id' => $image->id,
                                'url' => $image->url,
                                'title' => $image->title,
                                'description' => $image->description,
                                'width' => $image->width,
                                'height' => $image->height,
                                'uploaded_at' => date('d.m.Y', $image->uploaded_at),
                            ],
                            'preview' => [
                                'url' => $preview->url,
                                'width' => $preview->width,
                                'height' => $preview->height,
                            ]
                        ];
                    }
                }
                return $result;
            }
        }
        return ['success' => false];
    }

    public function actionSort()
    {
        $collectionId = Yii::$app->request->post('collection_id');
        $data = Yii::$app->request->post('data');
        $imagesIds = array_keys($data);
        $models = ImagesCollection2Images::findAll(['collection_id' => $collectionId, 'image_id' => $imagesIds]);
        if ($models) {
            foreach ($models as $model) {
                /* @var $model ImagesCollection2Images */
                ImagesCollection2Images::onCommit($model->id, [
                    'sort' => (integer) $data[$model->image_id]
                ]);
            }
        }
    }

}