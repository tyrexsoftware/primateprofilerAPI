<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use kartik\widgets\DatePicker;
use yii\bootstrap\Modal;



Modal::begin([
    'header' => '<h2>Hello world</h2>',
    'toggleButton' => ['label' => 'click me'],
]);
$form = ActiveForm::begin([
    'layout' => 'horizontal',
    'action' => ['contracts/save'],
    ]);

echo DatePicker::widget([
    'model' => $contracts,
    'attribute' => 'startdate',
    'attribute2' => 'enddate',
    'options' => ['placeholder' => 'Start date'],
    'options2' => ['placeholder' => 'End date'],
    'type' => DatePicker::TYPE_RANGE,
    'form' => $form,
    'pluginOptions' => [
        'autoclose' => true,
    ]
]);
echo $form->field($contracts, 'organization_id')->hiddenInput()->label(false);
?>
<div class="form-group">
<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
</div>
    <?php
    ActiveForm::end();

    Modal::end();
    ?>

