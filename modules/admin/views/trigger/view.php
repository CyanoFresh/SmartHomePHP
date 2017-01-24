<?php

/* @var $this yii\web\View */
/* @var $model app\models\Trigger */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Триггеры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trigger-view">

    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'active:boolean',
            [
                'attribute' => 'type',
                'value' => $model->getTypeLabel(),
            ],
            'date:datetime',
            'time:time',
            'weekdays',
            'item_id',
            'item_value',
            'name',
        ],
    ]) ?>

</div>
