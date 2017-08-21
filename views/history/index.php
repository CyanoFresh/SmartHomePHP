<?php

/* @var $this yii\web\View */
/* @var $searchModel \app\models\search\HistorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = 'История';
$this->params['breadcrumbs'][] = $this->title;
$this->params['in-card'] = false;
?>
<div class="history-index">

    <?php Pjax::begin(); ?>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'summaryOptions' => ['class' => 'alert alert-info'],
        'emptyTextOptions' => ['class' => 'alert alert-warning'],
        'itemView' => '_history',
    ]) ?>

    <?php Pjax::end(); ?>
</div>
