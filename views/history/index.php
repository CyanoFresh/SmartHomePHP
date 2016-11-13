<?php

/* @var $this yii\web\View */
/* @var $searchModel app\models\HistorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\Pjax;
$this->title = 'Histories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-index">

<!--    <p>-->
<!--        --><?//= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?>
<!--    </p>-->
<!--    --><?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Pjax::begin(); ?>
    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'summaryOptions' => ['class' => 'alert alert-info'],
        'itemView' => '_history',
    ]) ?>
    <?php Pjax::end(); ?>
</div>
