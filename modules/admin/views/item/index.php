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
$this->params['in-card'] = false;
?>

<div class="card table-card">
    <div class="table-card-actions">
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-default btn-flat']) ?>
        <?= Html::a('Обновить на сервере', [
            '/api/panel/update-items',
            'access-token' => Yii::$app->user->identity->api_key,
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
