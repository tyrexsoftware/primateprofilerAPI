<?php

namespace app\controllers;

use Yii;
use app\models\LoginForm;
use app\models\Staff;

class LoginController extends \app\addons\Controller {

    public $layout = 'login';


    public function actionIndex() {
        $model = new LoginForm();

        return $this->render('index', ['model' => $model]);
    }

    public function actionAuthenticate() {

        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('index', [
                        'model' => $model,
            ]);
        }
    }

    public function actionLogout() {
        Yii::$app->user->logout();

        return $this->goHome();
    }

}
