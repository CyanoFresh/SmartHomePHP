<?php

/* @var $this yii\web\View */
/* @var $searchModel app\models\ItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
$this->title = 'Items';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="item-index">

    <p>
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summaryOptions' => ['class' => 'alert alert-info'],
        'layout' => '{summary}<div class="table-responsive">{items}</div>{pager}',
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'type',
            'update_interval',
            'save_history',
            'room_id',
            // 'url:url',
            // 'name',
            // 'sort_order',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
