<?php

namespace app\addons\helpers;

use app\models\Observations;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Writer {

    public static function getValue($data, $key) {
        return isset($data[$key]) ? $data[$key] : null;
    }

    public static function writeObservation($observation_data, $id, $key) {

        $observationsModel = new Observations();
        
        $observationsModel->setScenario($key);
        $observationsModel->local_id = $id;
        $observationsModel->observer_id = self::getValue($observation_data, 'observer_id');
        $observationsModel->appkey = $key;
        $observationsModel->organization_id = self::getValue($observation_data, 'organization_id');
        $observationsModel->sync_user_id = \Yii::$app->user->id;
        $observationsModel->animal_id = self::getValue($observation_data, 'animal_id');
        $observationsModel->location = self::getValue($observation_data, 'location');
        $observationsModel->total_score = self::getValue($observation_data, 'total_score');
        $observationsModel->observation_date = self::getValue($observation_data, 'observation_date');
        $observationsModel->number_of_actions = self::getValue($observation_data, 'number_of_actions');
        $observationsModel->total_observation_time = self::getValue($observation_data,'total_observation_time');
        $observationsModel->timestart = self::getValue($observation_data, 'timestart');
        $observationsModel->timeend = self::getValue($observation_data,'timeend');




        return $observationsModel;
    }

}
