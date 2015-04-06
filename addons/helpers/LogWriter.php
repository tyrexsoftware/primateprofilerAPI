<?php

namespace app\addons\helpers;

use yii;
use app\models\AccessLog;

class LogWriter {
    
    public static function addrecord($access_type, $ud_id)
    {
        $model = new AccessLog();

        $model->access_type = $access_type;
        $model->organization_id = Yii::$app->user->identity->organization_id;
        $model->user_id = Yii::$app->user->identity->user_id;
        $model->udid = $ud_id;
        return $model->save();
    }
    
    public static function flatModelErrors($error) {
        $message = '';
        foreach ($error as $key => $issue) {
            foreach($issue as $errorMessage) {

                $message .= 'Field :'.$key. ' - '. $errorMessage. "\n";
            }
        }
        return $message;
    }
    
}