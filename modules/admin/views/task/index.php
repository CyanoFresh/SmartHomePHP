<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\TaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\models\Task;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Задачи';
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
            'name',
            [
                'filter' => Task::getTypes(),
                'attribute' => 'type',
                'value' => function (Task $model) {
                    return $model->getTypeLabel();
                },
            ],
//            'item_id',
//            'item_value',

            ['class' => 'app\components\ActionButtonColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
