<?php

/* @var $this yii\web\View */
/* @var $searchModel app\models\ItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\models\Board;
use app\models\Item;
use app\models\Room;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Элементы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="item-index">

    <p>
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Обновить на сервере', [
            '/api/panel/update-items',
            'access-token' => Yii::$app->user->identity->api_key,
        ], [
            'class' => 'btn btn-default ajax-call',
        ]) ?>
    </p>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summaryOptions' => ['class' => 'alert alert-info'],
        'tableOptions' => ['class' => 'table table-hover material-table'],
        'layout' => '{summary}<div class="table-responsive">{items}</div>{pager}',
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'contentOptions' => ['style' => 'width: 5%']
            ],
            'name',
            [
                'attribute' => 'room_id',
                'filter' => Room::getList(),
                'value' => function ($model) {
                    /** @var $model Item */
                    return $model->room->name;
                },
            ],
            [
                'attribute' => 'board_id',
                'filter' => Board::getList(),
                'value' => function ($model) {
                    /** @var $model Item */
                    return $model->board->name;
                },
            ],
            [
                'filter' => Item::getTypesArray(),
                'attribute' => 'type',
                'value' => function ($model) {
                    /** @var $model Item */
                    return $model->getTypeLabel();
                },
            ],
            'pin',

            ['class' => 'app\components\ActionButtonColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
