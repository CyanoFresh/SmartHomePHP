<?php

/* @var $this yii\web\View */
/* @var $model app\models\Room */

use yii\helpers\Html;

$this->title = 'Добавить Room';
$this->params['breadcrumbs'][] = ['label' => 'Rooms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="room-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
