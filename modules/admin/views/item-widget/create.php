<?php

/* @var $this yii\web\View */
/* @var $model app\models\ItemWidget */

use yii\helpers\Html;

$this->title = 'Добавить Item Widget';
$this->params['breadcrumbs'][] = ['label' => 'Item Widgets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="item-widget-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
