<?php

/* @var $this yii\web\View */
/* @var $model app\models\Board */

use yii\helpers\Html;

$this->title = 'Добавить Устройство';
$this->params['breadcrumbs'][] = ['label' => 'Устройства', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="board-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
