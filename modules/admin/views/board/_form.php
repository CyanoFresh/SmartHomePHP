<?php

/* @var $this yii\web\View */
/* @var $model app\models\Board */
/* @var $form yii\widgets\ActiveForm */

use app\models\Board;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

if ($model->isNewRecord) {
    $model->secret = md5(time());
    $model->remote_connection = false;
}

?>

<div class="board-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList(Board::getTypesArray(), [
        'prompt' => '--- выберите тип ---',
    ]) ?>

    <?= $form->field($model, 'secret')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'baseUrl')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'remote_connection')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
