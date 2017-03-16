<?php

/* @var $this yii\web\View */
/* @var $model app\models\User */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Пользователь ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'name',
            'email:email',
            [
                'attribute' => 'status',
                'value' => $model->getStatusLabel(),
            ],
            [
                'attribute' => 'group',
                'value' => $model->getGroupLabel(),
            ],
            [
                'attribute' => 'room_id',
                'value' => $model->room->name,
            ],
            'api_key',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
