<?php

/* @var $this yii\web\View */
/* @var $model app\models\Trigger */

use yii\helpers\Html;

$this->title = 'Добавить Триггер';
$this->params['breadcrumbs'][] = ['label' => 'Триггеры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trigger-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
