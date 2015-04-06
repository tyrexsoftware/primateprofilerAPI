<?php

namespace app\addons\rest;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\rest\Action;

class GetSettingsAction extends Action {

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

    private static function checkValues($data) {
        if (strpos($data, 'alopecia_status_') !== false ||
                strpos($data, 'alopecia_option_') !== false ||
                strpos($data, 'alopecia_color_') !== false) {
            return true;
        } else {
            return false;
        }
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
        $showbehaviors = false;

        foreach ($this->params as $key => $value) {
            if (in_array($key, $safeAttributes)) {
                $params[$key] = $value;
                if ($key =='appkey'&& $value == 'bhvtest') {
                    $showbehaviors = true;
                }
            }
        }

        if (empty($params)) {
            $showbehaviors = true;
        }

        $query = $modelClass::find();

        $params['organization_id'] = \Yii::$app->user->identity->organization_id;

        foreach ($params as $param => $value) {
            $query->andFilterWhere([
                $model->tablename() . '.' . $param => $value,
            ]);
        }
        $setting = array();

        $result = $query->all();
        foreach ($query->all() as $values) {
            $appkey = $values->appkey;
            unset($values->appkey);
            unset($values->organization_id);


            $check = self::checkValues($values->setting_name);
            if ($check !== false) {
                $id = substr($values->setting_name, -1);
                if (strpos($values->setting_name, 'alopecia_status_') !== false) {
                    $setting['appkey'][$appkey]['selectoptions'][$id]['status'] = $values->setting_value;
                }
                if (strpos($values->setting_name, 'alopecia_option_') !== false) {
                    $setting['appkey'][$appkey]['selectoptions'][$id]['name'] = $values->setting_value;
                }
                if (strpos($values->setting_name, 'alopecia_color_') !== false) {
                    $setting['appkey'][$appkey]['selectoptions'][$id]['color'] = $values->setting_value;
                }
            } else {
                $setting['appkey'][$appkey]['settings'][] = $values;
            }
        }

        if ($showbehaviors) {
            $behaviorsXML = simplexml_load_file(Yii::getAlias('@app') . '/config/mainbehavors.xsd');
            $behaviorsGrid = array();
            foreach ($behaviorsXML as $box => $values) {
                foreach ($values->value as $id => $conainers) {

                    $behaviorsGrid[(string) $values['name']][] = [
                        'name'=>(string) $conainers['name'], 
                        'linkable'=>null!==$conainers['linkable']&&(bool)$conainers['linkable']?true:false];
                }
            }
            $setting['appkey']['bhvtest']['behaviors'] = $behaviorsGrid;
            $setting['appkey']['bhvtest']['recepients'] = ['Self', 'Observer', 'Other'];
        }

        return $provider = new ArrayDataProvider([
            'allModels' => $setting,
        ]);
    }

}
