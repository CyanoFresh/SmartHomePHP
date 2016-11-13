<?php

/* @var $this yii\web\View */
/* @var $searchModel \app\models\HistorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
$this->title = 'Histories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-index">

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
            'item_id',
            'commited_at:datetime',
            'value',

            ['class' => 'app\components\ActionButtonColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
