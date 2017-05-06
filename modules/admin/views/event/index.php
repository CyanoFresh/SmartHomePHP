<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'События';
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
            [
                'attribute' => 'id',
                'contentOptions' => ['style' => 'width: 5%']
            ],
            'name',
            [
                'attribute' => 'active',
                'format' => 'boolean',
                'filter' => [
                    0 => 'Нет',
                    1 => 'Да',
                ],
            ],
            'description:ntext',
            'last_triggered_at:datetime',

            ['class' => 'app\components\ActionButtonColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
