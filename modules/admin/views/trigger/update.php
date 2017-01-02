<?php

/* @var $this yii\web\View */
/* @var $model app\models\Trigger */

use yii\helpers\Html;

$this->title = 'Изменить Триггер: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Триггеры', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="trigger-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
