<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Applications2organization;
use app\models\Applications;
use app\models\Organization;
use app\models\ApplicationSettings;
use app\models\Contracts;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;

class OrganizationController extends \app\addons\Controller {

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

    public function actionIndex() {

        $orgQ = Organization::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $orgQ,
            'pagination' => ['pagesize' => 5]
        ]);


        return $this->render('index', [
                    'dataProvider' => $dataProvider
        ]);
    }

    private static function addSettings($organizationId) {
        $settings_xml = simplexml_load_file(Yii::getAlias('@app') . '/config/availablesettings.xsd');
        $appsettings = [];
        foreach ($settings_xml->settings->application->appkey as $settings) {

            foreach ($settings as $indSetting) {
                $appsettings[] = [(string) $settings['name'], $organizationId, (string) $indSetting['name'], (string) $indSetting['value']];
            }
        }
        Yii::$app->db->createCommand()->batchInsert(
                ApplicationSettings::tableName(), [
            'appkey', 'organization_id', 'setting_name', 'setting_value'
                ], $appsettings)->execute();

    }

    public function actionSave() {

        $modelA2O = new \app\models\Applications2organization;
        $modelApps = new \app\models\Applications;
        $modelOrg = new \app\models\Organization;
        $contracts = new \app\models\Contracts;
        $newOrganization = false;

        if ($modelOrg->load(Yii::$app->request->post()) && $modelA2O->load(Yii::$app->request->post()) && $modelA2O->validate() && $modelOrg->validate()) {

            $organization_id = $modelOrg->organization_id;

            if (null != $organization_id && is_numeric($organization_id)) {
                $organizationDb = Organization::find()->where('organization_id=' . $organization_id)->one();
            } else {
                $organizationDb = new Organization();
                $newOrganization = true;
            }

            $organizationDb->organization_name = $modelOrg->organization_name;
            $organizationDb->organization_contactemail = $modelOrg->organization_contactemail;
            $organizationDb->status = $modelOrg->status;
            $organizationDb->save();

            if ($newOrganization) {
                self::addSettings($organizationDb->organization_id);
            }

            Applications2organization::deleteAll('organization_id=' . $organizationDb->organization_id);
            if (is_array($modelA2O->applications_id) && (count($modelA2O->applications_id) > 0)) {
                foreach ($modelA2O->applications_id as $app) {
                    $apps2Org = new Applications2organization();
                    $apps2Org->applications_id = $app;
                    $apps2Org->organization_id = $organizationDb->organization_id;
                    $apps2Org->save();
                }
            }
            Yii::$app->session->setFlash('Orgaization Saved');
            return $this->redirect('index');
        }
    }

    public function actionEdit() {

        $modelA2O = new \app\models\Applications2organization;
        $modelApps = new \app\models\Applications;
        $modelOrg = new \app\models\Organization;
        $contracts = new \app\models\Contracts;
        $applications = Applications::find()->all();

        if (null != Yii::$app->request->get('organization_id') && is_numeric(Yii::$app->request->get('organization_id'))) {

            $contracts->organization_id = $modelOrg->organization_id = Yii::$app->request->get('organization_id');
            $OrgDB = Organization::find()->where('organization_id=' . $modelOrg->organization_id)->one();
            $apps2Org = Applications2organization::find()->where(['organization_id' => $modelOrg->organization_id])->all();



            $modelA2O->applications_id = array_keys(ArrayHelper::map($apps2Org, 'applications_id', 'organization_id'));

            $modelOrg->organization_name = $OrgDB->organization_name;
            $modelOrg->organization_contactemail = $OrgDB->organization_contactemail;

            $modelOrg->status = $OrgDB->status;

            return $this->render('create', [
                        'modelOrg' => $modelOrg,
                        'modelApps' => $modelApps,
                        'modelA2O' => $modelA2O,
                        'applications' => $applications,
                        'contracts' => $contracts
            ]);
        }
    }

    public function actionCreate() {


        $modelA2O = new \app\models\Applications2organization;
        $modelApps = new \app\models\Applications;
        $modelOrg = new \app\models\Organization;
        $contracts = new \app\models\Contracts;

        $applications = Applications::find()->all();


        // either the page is initially displayed or there is some validation error
        return $this->render('create', [
                    'modelOrg' => $modelOrg,
                    'modelApps' => $modelApps,
                    'modelA2O' => $modelA2O,
                    'applications' => $applications,
                    'contracts' => $contracts
        ]);
    }

}
