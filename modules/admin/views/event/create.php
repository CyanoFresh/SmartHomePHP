<?php

/* @var $this yii\web\View */
/* @var $model app\models\Event */

use yii\helpers\Html;

$this->title = 'Добавить Event';
$this->params['breadcrumbs'][] = ['label' => 'Events', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="event-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
