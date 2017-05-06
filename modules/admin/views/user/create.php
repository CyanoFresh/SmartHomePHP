<?php

/* @var $this yii\web\View */
/* @var $model app\models\User */

use yii\helpers\Html;

$this->title = 'Добавить Пользователя';
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
