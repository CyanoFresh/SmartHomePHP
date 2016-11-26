<?php

/* @var $this yii\web\View */
/* @var $model app\models\Event */
/* @var $form yii\widgets\ActiveForm */

use app\models\Event;
use app\models\Item;
use app\models\Task;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="event-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'type')->dropDownList(Event::getStatuses(), [
        'prompt' => '-- выберите тип --'
    ]) ?>

    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'trig_time')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'trig_time_wdays')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?= $form->field($model, 'trig_date')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'trig_item_id')->dropDownList(Item::getList(), [
        'prompt' => '-- выберите элемент --'
    ]) ?>

    <?= $form->field($model, 'trig_item_value')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'task_id')->dropDownList(Task::getList(), [
        'prompt' => '-- выберите задачу --'
    ]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить',
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
