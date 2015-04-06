<?php

namespace app\addons\rest;

use Yii;
use yii\data\ArrayDataProvider;
use yii\rest\Action;
use app\addons\helpers\LogWriter;
use yii\helpers\Json;

class SyncAction extends Action {

    /**
     * @var callable a PHP callable that will be called to prepare a data provider that
     * should return a collection of the models. If not set, [[prepareDataProvider()]] will be used instead.
     * The signature of the callable should be:
     *
     * ```php
     * function ($action) {
     *     // $action is the action object currently running
     * }
     * ```
     *
     * The callable should return an instance of [[ActiveDataProvider]].
     */
    public $prepareDataProvider;
    public $params;

    /**
     * @return ActiveDataProvider
     */
    public function run() {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }
        if (null !== Yii::$app->request->post('udid')) {
            LogWriter::addRecord($this->id, Yii::$app->request->post('udid'));
        } else {
            throw new \yii\web\HttpException(400, 'UDID is missing');
        }
        return $this->prepareDataProvider();
    }

    /**
     * Prepares the data provider that should return the requested collection of the models.
     * @return ActiveDataProvider
     */
    protected function prepareDataProvider() {
        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this);
        }

        //if (null === Yii::$app->request->post('syncdata') || empty(Yii::$app->request->post('syncdata'))) {
        if (null === Yii::$app->request->post('syncdata')) {
            throw new \yii\web\HttpException(400, 'Nothing to Sync');
        }
        $syncdata = Json::decode(Yii::$app->request->post('syncdata'));

        $writerClassObject = [];
        
        foreach ($syncdata['apptype'] as $key => $observations) {

            $classname = '\\app\\addons\\helpers\\writers\\' . $key . 'Writer';
            $writerClassObject[$key] = $classname::writeObservations($observations);
        }
        return $provider = new ArrayDataProvider([
            'allModels' => $writerClassObject,
        ]);
    }

}
