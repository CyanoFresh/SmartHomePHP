<?php

/* @var $this yii\web\View */
/* @var $model app\models\ItemWidget */

use yii\helpers\Html;

$this->title = 'Изменить Item Widget: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Item Widgets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="item-widget-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
