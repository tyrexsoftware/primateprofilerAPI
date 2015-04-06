<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url
?>

<?php $form = ActiveForm::begin(['layout' => 'horizontal', 'action'=>['organization/save']]) ?>
<?= $form->field($modelOrg, 'organization_name') ?>

<?= $form->field($modelOrg, 'organization_contactemail')->input('email') ?>
<?= $form->field($modelOrg, 'status')->dropDownList([1 => 'Enabled', 0 => 'Disabled']) ?>

<?=
        $form->field($modelA2O, 'applications_id')->
        checkboxList(ArrayHelper::map($applications, 'applications_id', 'appname'))
?>

<?= $form->field($modelA2O, 'organization_id')->hiddenInput()->label(false); ?>
<?= $form->field($modelOrg, 'organization_id')->hiddenInput()->label(false); ?>


<div class="form-group">
    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>