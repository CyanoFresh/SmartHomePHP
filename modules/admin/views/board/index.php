<?php

/* @var $this yii\web\View */
/* @var $searchModel \app\models\search\BoardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\models\Board;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Устройства';
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
                'attribute' => 'type',
                'filter' => Board::getTypesArray(),
                'value' => function (Board $model) {
                    return $model->getTypeLabel();
                }
            ],
            [
                'filter' => [
                    0 => 'Нет',
                    1 => 'Да',
                ],
                'format' => 'boolean',
                'attribute' => 'remote_connection',
            ],

            ['class' => 'app\components\ActionButtonColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>
