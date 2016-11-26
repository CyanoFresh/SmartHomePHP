<?php

/* @var $this yii\web\View */
/* @var $model app\models\TaskAction */

use yii\helpers\Html;

$this->title = 'Изменить Task Action: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Task Actions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="task-action-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
