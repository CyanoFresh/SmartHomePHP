<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\ItemWidgetSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\models\Item;
use app\models\ItemWidget;
use app\models\Room;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Виджеты';
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
            'icon',
            [
                'attribute' => 'room_id',
                'filter' => Room::getList(),
                'value' => function ($model) {
                    /** @var $model ItemWidget */
                    return $model->room->name;
                },
            ],
            [
                'attribute' => 'item_id',
                'filter' => Item::getList(),
                'value' => function ($model) {
                    /** @var $model ItemWidget */
                    return $model->item->name;
                },
            ],
            'active:boolean',

            ['class' => 'app\components\ActionButtonColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
