<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
?>
<?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'first_name') ?>
    <?= $form->field($model, 'last_name') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= $form->field($model, 'email')->input('email')  ?>
    <?= $form->field($model, 'organization_id')->
        dropDownList(ArrayHelper::map($organizations, 'organization_id', 'organization_name'))  ?>
    <?php /* $form->field($model, 'organization_id') ?>
    <?= $form->field($model, 'organization_id')->dropDownList(['a','b'])) */?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>