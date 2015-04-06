<?php

namespace app\controllers;

use Yii;
use yii\filters\auth\QueryParamAuth;
use app\addons\ActiveController;
class RestwebController extends ActiveController {

    public $modelClass = 'app\models\AnimalsSync';

    public function init() {
        \Yii::$app->user->enableSession = false;
        \Yii::$app->set('user', ['class' => 'yii\web\User',
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true]);

        parent::init();
    }

    public function behaviors() {
        $behaviors = parent::behaviors();

        if (Yii::$app->urlManager->parseRequest(Yii::$app->request)[0] !== 'restweb/login') {
            $behaviors['authenticator'] = [
                'class' => QueryParamAuth::className(),
            ];
        }
        return $behaviors;
    }

    public function actions() {

        $actions = [
            'viewuseranimals' => [
                'class' => 'app\addons\rest\ViewAction',
                'modelClass' => 'app\models\AnimalsSync',
                'checkAccess' => [$this, 'checkAccess'],
                'params' => \Yii::$app->request->get()
            ],
            'viewuserproperties' => [
                'class' => 'app\addons\rest\ViewAction',
                'modelClass' => 'app\models\User',
                'checkAccess' => [$this, 'checkAccess'],
                'params' => \Yii::$app->request->get()
            ],
            'getsettings' => [
                'class' => 'app\addons\rest\GetSettingsAction',
                'modelClass' => 'app\models\ApplicationSettings',
                'checkAccess' => [$this, 'checkAccess'],
                'params' => \Yii::$app->request->get()
            ],
            'login' => [
                'class' => 'app\addons\rest\LoginAction',
                'modelClass' => 'app\models\User',
                'checkAccess' => [$this, 'checkAccess'],
                'params' => \Yii::$app->request->get()
            ],
            'sync' => [
                'class' => 'app\addons\rest\SyncAction',
                'modelClass' => 'app\models\AccessLog',
                'checkAccess' => [$this, 'checkAccess'],
                'params' => \Yii::$app->request->post()
            ],
        ];

        return array_merge(parent::actions(), $actions);
    }

    public function verbs() {

        $verbs = [
            'viewuseranimals' => ['GET'],
            'viewuserproperties' => ['GET'],
            'getsettings' => ['GET'],
            'login' => ['POST'],
            'sync' => ['POST']
        ];
        return array_merge(parent::verbs(), $verbs);
    }

}
