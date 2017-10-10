<?php

namespace snewer\images\models;

use Yii;

trait ImagesCommitsAndResetsTrait
{

    public static function getSessionCommitKey($modelId)
    {
        return md5(__CLASS__) . '_c_' . $modelId;
    }

    public static function getSessionResetKey($modelId)
    {
        return md5(__CLASS__) . '_r_' . $modelId;
    }

    public static function onCommit($id, $setAttrs = null, $delete = null)
    {
        Yii::$app->session->set(self::getSessionCommitKey($id), [
            'setAttrs' => $setAttrs,
            'delete' => $delete
        ]);
    }

    public static function onReset($id, $setAttrs = null, $delete = null)
    {
        Yii::$app->session->set(self::getSessionResetKey($id), [
            'setAttrs' => $setAttrs,
            'delete' => $delete
        ]);
    }

    public function commit()
    {
        $commitData = Yii::$app->session->get(self::getSessionCommitKey($this->id));
        if ($commitData) {
            if (isset($commitData['delete']) && $commitData['delete'] == true) {
                $this->delete();
            } else {
                if (isset($commitData['setAttrs']) && is_array($commitData['setAttrs'])) {
                    foreach ($commitData['setAttrs'] as $key => $value) {
                        $this->{$key} = $value;
                    }
                    $this->save();
                }
            }
            Yii::$app->session->remove(self::getSessionCommitKey($this->id));
        }

        $resetKey = self::getSessionResetKey($this->id);
        if (Yii::$app->session->has($resetKey)) {
            Yii::$app->session->remove($resetKey);
        }
    }

    public function reset()
    {
        $resetData = Yii::$app->session->get(self::getSessionResetKey($this->id));
        if ($resetData) {
            if (isset($resetData['delete']) && $resetData['delete'] === true) {
                $this->delete();
            } else {
                if(isset($resetData['setAttrs']) && is_array($resetData['setAttrs'])) {
                    foreach ($resetData['setAttrs'] as $key => $value) {
                        $this->{$key} = $value;
                    }
                    $this->save();
                }
            }
        }

        $commitKey = self::getSessionCommitKey($this->id);
        if (Yii::$app->session->has($commitKey)) {
            Yii::$app->session->remove($commitKey);
        }
    }

}