<?php

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <p><?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summaryOptions' => ['class' => 'alert alert-info'],
        'layout' => '{summary}<div class="table-responsive">{items}</div>{pager}',
        'columns' => [
            [
                'attribute' => 'id',
                'contentOptions' => ['style' => 'width: 5%']
            ],
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
</div>
