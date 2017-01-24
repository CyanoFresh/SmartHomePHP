<?php

/* @var $this yii\web\View */
/* @var $model app\models\Event */
/* @var $form yii\widgets\ActiveForm */

use app\models\Task;
use app\models\Trigger;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

if ($model->isNewRecord) {
    $model->active = true;
}
?>

<div class="event-form">

    <?php $form = ActiveForm::begin(); ?>

    <h2>Основная информация</h2>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'active')->checkbox() ?>

    <h2>Условия</h2>

    <?= $form->field($model, 'trigger_ids')->widget(Select2::className(), [
        'data' => Trigger::getList(),
        'showToggleAll' => false,
        'options' => [
            'placeholder' => 'Выберите триггеры ...',
            'multiple' => true,
        ],
    ]) ?>

    <h2>Задачи</h2>

    <?= $form->field($model, 'task_ids')->widget(Select2::className(), [
        'data' => Task::getList(),
        'showToggleAll' => false,
        'options' => [
            'placeholder' => 'Выберите задачи ...',
            'multiple' => true,
        ],
    ]) ?>

    <div class="form-group">
        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('Добавить и посмотреть', ['class' => 'btn btn-success', 'name' => 'after', 'value' => 'view']) ?>
            <?= Html::submitButton('Добавить и добавить еще', ['class' => 'btn btn-success', 'name' => 'after', 'value' => 'add-another']) ?>
            <?= Html::submitButton('Добавить и вернутся', ['class' => 'btn btn-success', 'name' => 'after', 'value' => 'return']) ?>
        <?php else: ?>
            <?= Html::submitButton('Сохранить и посмотреть', ['class' => 'btn btn-primary', 'name' => 'after', 'value' => 'view']) ?>
            <?= Html::submitButton('Сохранить и добавить еще', ['class' => 'btn btn-primary', 'name' => 'after', 'value' => 'add-another']) ?>
            <?= Html::submitButton('Сохранить и вернутся', ['class' => 'btn btn-primary', 'name' => 'after', 'value' => 'return']) ?>
        <?php endif; ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
