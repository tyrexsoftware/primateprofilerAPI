<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Tabs;
use yii\web\View;


echo Tabs::widget([
    'items' => [
        [
            'label' => 'Organization Information',
            'content' => $this->render('tabforms/organizationform', [ 'modelOrg' => $modelOrg, 'modelA2O' => $modelA2O, 'applications' => $applications]),
            'active' => false
        ],
        [ 'label' => 'Contracts',
            'content' => $this->render('tabforms/contractsform', ['contracts'=>$contracts]),
            'active' => true]
]]);


//$form->field($model, $attribute)->widget($class)
?>



