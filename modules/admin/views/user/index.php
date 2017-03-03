<?php

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\models\User;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
$this->params['in-card'] = false;
?>

<div class="card table-card">
    <div class="table-card-actions">
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-default btn-flat']) ?>
    </div>

    <?php Pjax::begin(); ?>
    <?= \app\widgets\DataTable::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'username',
            'email',
            [
                'filter' => User::getStatuses(),
                'attribute' => 'status',
                'value' => function ($model) {
                    /** @var $model User */
                    return $model->getStatusLabel();
                },
            ],
            [
                'filter' => User::getGroups(),
                'attribute' => 'group',
                'value' => function ($model) {
                    /** @var $model User */
                    return $model->getGroupLabel();
                },
            ],

            ['class' => 'app\components\ActionButtonColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
