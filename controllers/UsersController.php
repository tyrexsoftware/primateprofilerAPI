<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\User;
use app\models\Organization;
use yii\data\Pagination;
use yii\web\UploadedFile;
use app\models\CsvuploadForm;
use app\models\Animals;
use yii\base\Security;
use yii\data\ActiveDataProvider;

class UsersController extends \app\addons\Controller {

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

        
                $orgQ = User::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $orgQ,
            'pagination' => ['pagesize' => 5]
        ]);


        return $this->render('index', [
                    'dataProvider' => $dataProvider
        ]);

    }

    public function actionCreate() {
        $model = new \app\models\User;

        $organizations = Organization::find()->where('status!=0')->all();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            // valid data received in $model
            // do something meaningful here about $model ...array('model' => $model, 'organizations' => $organizations));
            $security = new Security ();

            $UserDb = new User();
            $UserDb->first_name = $model->first_name;
            $UserDb->last_name = $model->last_name;
            $UserDb->password = $model->password;
            $UserDb->email = $model->email;
            $UserDb->organization_id = $model->organization_id;
            $UserDb->address_id = 1;
            if (!empty($model->password)) {
                $UserDb->password = $security->generatePasswordHash($model->password);
                $UserDb->md5password = md5($model->password);
            }


            $UserDb->save();


            return $this->redirect('users/index');
        } else {

            // either the page is initially displayed or there is some validation error
            return $this->render('create', array('model' => $model, 'organizations' => $organizations));
        }
    }

    public function actionUploadanimals() {

        $model = new CsvuploadForm();

        if (Yii::$app->request->isPost) {


            if ($model->load(Yii::$app->request->post()) && $model->validate()) {

                $animalscsv = UploadedFile::getInstance($model, 'animalscsv');
                $userId = $model->user_id;

                $newFilePath = Yii::$app->params['tempdir'] .'\\'. "{$animalscsv->name}";
                $uploadSuccess = $animalscsv->saveAs($newFilePath);
                if (!$uploadSuccess) {
                    throw new CHttpException('Error uploading file.');
                }
                $animalsList = array_map('str_getcsv', file($newFilePath));

                foreach (array_slice($animalsList, 1) as $animal) {
                    $usersDb = new Animals();
                    $usersDb->saveAnimals($animal, $userId);
                }
            }

            //echo '<pre>';
            //print_r($animalsList);
            //echo '</pre>';
            //$animlasDB = new Animals();



            unlink($newFilePath);

            /* if ($model->validate()) {
              $model->file->saveAs('uploads/' . $model->file->baseName . '.' . $model->file->extension);
              } */
        } elseif (null != Yii::$app->request->get('user_id') && is_numeric(Yii::$app->request->get('user_id'))) {
            $user_id = Yii::$app->request->get('user_id');

            $model->user_id = $user_id;
            return $this->render('uploadanimals', array('model' => $model));
        } else {

            Yii::error("Tried dividing by zero.");
        }
    }

}
