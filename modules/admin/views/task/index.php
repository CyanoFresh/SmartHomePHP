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
?>
<div class="task-index">

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
            [
                'filter' => Task::getTypes(),
                'attribute' => 'type',
                'value' => function (Task $model) {
                    return $model->getTypeLabel();
                },
            ],
//            'item_id',
//            'item_value',
            'name',

            ['class' => 'app\components\ActionButtonColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
