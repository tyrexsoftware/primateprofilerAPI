<?php

namespace app\addons\rest;

use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\Action;

class ViewAction extends Action {

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

        $model = new $this->modelClass([
        ]);

        $safeAttributes = $model->safeAttributes();


        $params = array();

        foreach ($this->params as $key => $value) {
            if (in_array($key, $safeAttributes)) {
                $params[$key] = $value;
            }
        }

        $params['user_id'] = \Yii::$app->user->id;
        $organization = new \app\models\Organization();
        if ($organization->getOrganizationStateByUser($params['user_id'])->status == 0) {
            throw new \yii\web\HttpException(404, 'The organization or user has been blocked');
        }


        $query = $modelClass::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
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
            'pagination' => false,
        ]);
        



        return $dataProvider;
    }

}
