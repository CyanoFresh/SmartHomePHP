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
    </p>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summaryOptions' => ['class' => 'alert alert-info'],
        'layout' => '{summary}<div class="table-responsive">{items}</div>{pager}',
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