<?php

/* @var $this yii\web\View */
/* @var $model app\models\Task */
/* @var $form yii\widgets\ActiveForm */

use app\models\Item;
use app\models\Task;
use app\models\Trigger;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="task-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-sm-4">
            <h2>Основное</h2>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'type')->dropDownList(Task::getTypes(), [
                'prompt' => '--- выберите тип ---'
            ]) ?>
        </div>
        <div class="col-sm-4">
            <h2>Выполнить</h2>

            <?= $form->field($model, 'item_id')->widget(Select2::className(), [
                'data' => Item::getList(),
                'options' => [
                    'placeholder' => 'Выберите элемент ...',
                ],
            ]) ?>

            <?= $form->field($model, 'item_value')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'text')->textarea(['rows' => 3]) ?>
        </div>
        <div class="col-sm-4">
            <h2>Активируется через</h2>

            <?= $form->field($model, 'trigger_ids')->widget(Select2::className(), [
                'data' => Trigger::getList(),
                'showToggleAll' => false,
                'options' => [
                    'placeholder' => 'Выберите триггеры ...',
                    'multiple' => true,
                ],
            ]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
