<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'События';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="event-index">

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
            [
                'attribute' => 'id',
                'contentOptions' => ['style' => 'width: 5%']
            ],
            'name',
            [
                'attribute' => 'active',
                'format' => 'boolean',
                'filter' => [
                    0 => 'Нет',
                    1 => 'Да',
                ],
            ],
            'description:ntext',
            'last_triggered_at:datetime',

            ['class' => 'app\components\ActionButtonColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
