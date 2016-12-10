<?php

/* @var $this yii\web\View */
/* @var $searchModel app\models\BoardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\models\Board;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Устройства';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="board-index">

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
            [
                'attribute' => 'id',
                'contentOptions' => ['style' => 'width: 5%']
            ],
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
