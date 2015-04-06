<?php

namespace app\addons\helpers\writers;

use Yii;
use app\models\NovelobjectActions;
use app\models\Observations;
use app\addons\helpers\LogWriter;

class nvobjtestWriter extends \app\addons\helpers\Writer {

    public static $appkey = 'nvobjtest';

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
            $observation_data['number_of_actions'] = 1;
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
                    $actionsModel = new NovelobjectActions();
                    $actionsModel->observation_id = $observationsModel->observation_id;
                    $actionsModel->bhvtest_completed = parent::getValue($individualActions, 'bhvtest_completed');
                    $actionsModel->bhvtest_completion_date = parent::getValue($individualActions, 'bhvtest_completion_date');
                    $actionsModel->testdate = parent::getValue($individualActions, 'testdate');
                    $actionsModel->item_type = parent::getValue($individualActions, 'item_type');
                    $actionsModel->novel_item = parent::getValue($individualActions, 'novel_item');
                    $actionsModel->approach_time = parent::getValue($individualActions, 'approach_time');
                    $actionsModel->approach_comment = parent::getValue($individualActions, 'approach_comment');
                    $actionsModel->touch_time = parent::getValue($individualActions, 'touch_time');
                    $actionsModel->touch_comment = parent::getValue($individualActions, 'touch_comment');
                    $actionsModel->manipulate_time = parent::getValue($individualActions, 'manipulate_time');
                    $actionsModel->manipulate_comment = parent::getValue($individualActions, 'manipulate_comment');


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
