<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Contracts;
use yii\helpers\ArrayHelper;

class ContractsController extends \app\addons\Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    // allow authenticated users
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                // everything else is denied
                ],
            ],
        ];
    }

    public function actionCreate() {
        $contracts = new \app\models\Contracts;

        if ($contracts->load(Yii::$app->request->post()) & $contracts->validate()) {
            $contractsDb = new Contracts();
            $contractsDb->organization_id = $contracts->organization_id;
            $contractsDb->startdate = $contracts->startdate;
            $contractsDb->enddate = $contracts->enddate;
            $contractsDb->save();
        }
    }

}
