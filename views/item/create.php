<?php

/* @var $this yii\web\View */
/* @var $model app\models\Item */

use yii\helpers\Html;

$this->title = 'Добавить Item';
$this->params['breadcrumbs'][] = ['label' => 'Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="item-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
