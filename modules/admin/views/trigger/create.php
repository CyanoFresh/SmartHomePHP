<?php

/* @var $this yii\web\View */
/* @var $model app\models\Trigger */

use yii\helpers\Html;

$this->title = 'Добавить Trigger';
$this->params['breadcrumbs'][] = ['label' => 'Triggers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trigger-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
