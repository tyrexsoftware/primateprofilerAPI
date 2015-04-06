<?php

namespace app\addons\rest;

use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\Action;
use app\models\User;
use app\addons\helpers\LogWriter;

class LoginAction extends Action {

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

        /**
         * @var \yii\db\BaseActiveRecord $modelClass
         */
        $modelClass = $this->modelClass;

        $model = new User([
        ]);

        $safeAttributes = $model->safeAttributes();


        $params = array();

        $params['user_id'] = $model->restAuth(Yii::$app->request->post('username'), Yii::$app->request->post('password'));

        if (null !== Yii::$app->request->post('udid')) {
            LogWriter::addRecord($this->id, Yii::$app->request->post('udid'));
        } else {
            throw new \yii\web\HttpException(400, 'UDID is missing');
        }
        if (null === Yii::$app->request->post('username') || null === Yii::$app->request->post('password')) {
            throw new \yii\web\HttpException(400, 'Information about user or password is missing');
        }


        $organization = new \app\models\Organization();
        if ($organization->getOrganizationStateByUser($params['user_id'])->status == 0) {
            throw new \yii\web\HttpException(403, 'The organization or user has been blocked');
        }


        foreach ($this->params as $key => $value) {
            if (in_array($key, $safeAttributes)) {
                $params[$key] = $value;
            }
        }


        $query = $modelClass::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (empty($params)) {
            return $dataProvider;
        }


        foreach ($params as $param => $value) {
            $query->andFilterWhere([
                $model->tablename() . '.' . $param => $value,
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        return $dataProvider;
    }

}
