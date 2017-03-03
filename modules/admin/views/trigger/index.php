<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\TriggerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\models\Trigger;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Триггеры';
$this->params['breadcrumbs'][] = $this->title;
$this->params['in-card'] = false;
?>

<div class="card table-card">
    <div class="table-card-actions">
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-default btn-flat']) ?>
        <?= Html::a('Обновить на сервере', [
            '/api/panel/schedule-triggers',
            'access-token' => Yii::$app->user->identity->api_key
        ], [
            'class' => 'btn btn-default btn-flat ajax-call',
        ]) ?>
    </div>

    <?php Pjax::begin(); ?>
    <?= \app\widgets\DataTable::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'name',
            [
                'filter' => Trigger::getTypes(),
                'attribute' => 'type',
                'value' => function (Trigger $model) {
                    return $model->getTypeLabel();
                },
            ],
            [
                'attribute' => 'active',
                'format' => 'boolean',
                'filter' => [
                    0 => 'Нет',
                    1 => 'Да',
                ],
            ],
//            'trig_date',
//            'trig_time',
//            'trig_time_wdays',
//            'trig_item_id',
//            'trig_item_value',

            ['class' => 'app\components\ActionButtonColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
