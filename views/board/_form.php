<?php

/* @var $this yii\web\View */
/* @var $model app\models\Board */
/* @var $form yii\widgets\ActiveForm */

use app\models\Board;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="board-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList(Board::getTypesArray()) ?>

    <?= $form->field($model, 'secret')->textInput() ?>

    <?= $form->field($model, 'baseUrl')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
