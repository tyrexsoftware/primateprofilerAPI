<?php

namespace app\addons\helpers\writers;

use Yii;
use app\models\Actions;
use app\models\Observations;
use app\models\BehaviourActions;
use app\addons\helpers\LogWriter;

class bhvtestWriter extends \app\addons\helpers\Writer {

    public static $appkey = 'bhvtest';

    public static function writeObservations($observations) {

        $observedAnimals = 0;
        $totalNumberOfActions = 0;
        $connection = \Yii::$app->db;
        $mainError = false;

//        Yii::info('transaction_began', 'sync');


        foreach ($observations as $observation_id => $observation_data) {
            $transaction = $connection->beginTransaction();
            $error = false;
            $message = [];
            $numberOfActions = 0;

            $observationsModel = parent::writeObservation($observation_data, $observation_id, self::$appkey);


            if (false === $observationsModel->save()) {

                $error = true;
                $mainError = true;
                $message[] = LogWriter::flatModelErrors($observationsModel->getErrors());
                Yii::info(LogWriter::flatModelErrors($observationsModel->getErrors()), 'sync');
                $transaction->rollBack();
                break;
            } else {

                foreach ($observation_data['actions'] as $internal_id => $individualActions) {
                    $actionsModel = new BehaviourActions();
                    $actionsModel->observation_id = $observationsModel->observation_id;
                    $actionsModel->start_time = $individualActions['start_time'];
                    $actionsModel->end_time = $individualActions['end_time'];
                    $actionsModel->duration = $individualActions['duration'];
                    $actionsModel->container = $individualActions['container'];
                    $actionsModel->action = $individualActions['selection'];
                    $actionsModel->animal_id_connection = $individualActions['animal_id_connection'];
                    $actionsModel->comment = $individualActions['comment'];


                    if (false === $actionsModel->save()) {
                        $error = true;
                        $mainError = true;
                        $message[] = LogWriter::flatModelErrors($actionsModel->getErrors());
                        Yii::info(LogWriter::flatModelErrors($actionsModel->getErrors()), 'sync');
                        $transaction->rollBack();
                        break;
                    } else {
                        $numberOfActions++;
                    }
                }
                if (!$error) {
                    $transaction->commit();
                    $observedAnimals ++;
                    $totalNumberOfActions = $totalNumberOfActions + $numberOfActions;
                }
            }
        }

        return ([
            'status' => true,
            'haserrors' => $mainError,
            'observed_animals' => $observedAnimals,
            'number_of_actions' => $totalNumberOfActions,
        ]);
    }

}
