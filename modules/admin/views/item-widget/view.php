<?php

/* @var $this yii\web\View */
/* @var $model app\models\ItemWidget */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Item Widgets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="item-widget-view">

    <p>
        <?= Html::a('<i class="fa fa-pencil"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fa fa-trash"></i>', ['delete', 'id' => $model->id], [
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
            'active',
            'sort_order',
            'item_id',
            'room_id',
            'name',
            'html_class',
            'icon',
        ],
    ]) ?>

</div>
