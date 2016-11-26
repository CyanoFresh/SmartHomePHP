<?php

/* @var $this yii\web\View */
/* @var $model app\models\TaskAction */

use yii\helpers\Html;

$this->title = 'Добавить Task Action';
$this->params['breadcrumbs'][] = ['label' => 'Task Actions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="task-action-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
