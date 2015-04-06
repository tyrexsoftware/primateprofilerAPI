<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\grid\ActionColumn;
?>
<h1>organization/index</h1>


<?php \yii\widgets\Pjax::begin(); ?>
<?=
GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'first_name',
        'last_name',
        'email',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update}{delete}',
            'urlCreator' => function ( $action, $model, $key, $index ) {
        if ($action = "update") {
            return Url::to(['users/edit', 'user_id' => $model->organization_id]);
        }
    },
        ],
    ]]
);
\yii\widgets\Pjax::end();           
?>