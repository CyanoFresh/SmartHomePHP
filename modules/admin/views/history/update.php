<?php

/* @var $this yii\web\View */
/* @var $model app\models\History */

use yii\helpers\Html;

$this->title = 'Изменить History: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Histories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="history-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
