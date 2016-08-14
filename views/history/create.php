<?php

/* @var $this yii\web\View */
/* @var $model app\models\History */

use yii\helpers\Html;

$this->title = 'Добавить History';
$this->params['breadcrumbs'][] = ['label' => 'Histories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
