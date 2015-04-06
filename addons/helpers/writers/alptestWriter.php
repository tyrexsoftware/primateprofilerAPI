<?php

namespace app\addons\helpers\writers;

use Yii;
use app\models\AlopeciaActions;
use app\models\Observations;
use app\addons\helpers\LogWriter;

class alptestWriter extends \app\addons\helpers\Writer {

    public static $appkey = 'alptest';

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
                    $actionsModel = new AlopeciaActions();
                    $actionsModel->observation_id = $observationsModel->observation_id;
                    $actionsModel->bodypart = $individualActions['bodypart'];
                    $actionsModel->alopecia_type = $individualActions['alopecia_type'];
                    $actionsModel->color = $individualActions['color'];
                    $actionsModel->percentage = $individualActions['percentage'];
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
