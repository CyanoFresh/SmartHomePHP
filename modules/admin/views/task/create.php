<?php

/* @var $this yii\web\View */
/* @var $model app\models\Task */

use yii\helpers\Html;

$this->title = 'Добавить Task';
$this->params['breadcrumbs'][] = ['label' => 'Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="task-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
