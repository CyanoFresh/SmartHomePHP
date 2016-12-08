<?php

/* @var $this yii\web\View */
/* @var $model app\models\Item */

use yii\helpers\Html;

$this->title = 'Изменить Элемент: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Элементы', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="item-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
