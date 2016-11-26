<?php

/* @var $this yii\web\View */
/* @var $model app\models\TaskAction */
/* @var $form yii\widgets\ActiveForm */

use app\models\Item;
use app\models\Task;
use app\models\TaskAction;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="task-action-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'task_id')->dropDownList(Task::getList(), [
        'prompt' => '-- выберите задачу --',
    ]) ?>

    <?= $form->field($model, 'type')->dropDownList(TaskAction::getTypes(), [
        'prompt' => '-- выберите тип --',
    ]) ?>

    <?= $form->field($model, 'item_id')->dropDownList(Item::getList(), [
        'prompt' => '-- выберите элемент --',
    ]) ?>

    <?= $form->field($model, 'item_value')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
