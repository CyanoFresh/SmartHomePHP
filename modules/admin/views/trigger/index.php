<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\TriggerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\models\Trigger;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Triggers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trigger-index">

    <p>
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Обновить', [
            '/api/panel/schedule-triggers',
            'access-token' => Yii::$app->user->identity->api_key
        ], [
            'class' => 'btn btn-default schedule-triggers',
        ], [
            'sdfsdfsd' => 'sdfsdf',
        ]) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summaryOptions' => ['class' => 'alert alert-info'],
        'layout' => '{summary}<div class="table-responsive">{items}</div>{pager}',
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'filter' => [
                    0 => 'Нет',
                    1 => 'Да',
                ],
                'format' => 'boolean',
                'attribute' => 'active',
            ],
            'name',
            [
                'filter' => Trigger::getTypes(),
                'attribute' => 'type',
                'value' => function (Trigger $model) {
                    return $model->getTypeLabel();
                },
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
