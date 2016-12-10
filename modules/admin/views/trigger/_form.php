<?php

/* @var $this yii\web\View */
/* @var $model app\models\Trigger */
/* @var $form yii\widgets\ActiveForm */

use app\models\Item;
use app\models\Trigger;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\time\TimePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

if ($model->isNewRecord) {
    $model->active = true;
}

?>

<div class="trigger-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-sm-3">
            <h2>Основное</h2>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'type')->dropDownList(Trigger::getTypes(), [
                'prompt' => '--- выберите тип ---'
            ]) ?>

            <?= $form->field($model, 'active')->checkbox() ?>
        </div>
        <div class="col-sm-5">

            <h2>Условия</h2>

            <div class="row">
                <div class="col-sm-6">
                    <?= $form->field($model, 'trig_time')->widget(TimePicker::className(), [
                        'pluginOptions' => [
                            'showMeridian' => false,
                            'defaultTime' => false,
                        ],
                    ]) ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model, 'trig_time_wdays')->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <?= $form->field($model, 'trig_date')->widget(DateControl::className(), [
                'type' => DateControl::FORMAT_DATETIME,
                'displayTimezone' => Yii::$app->formatter->timeZone,
                'saveFormat' => 'php:U',
                'options' => [
                    'pluginOptions' => [
                        'todayHighlight' => true,
                        'todayBtn' => true,
                    ],
                ],
            ]) ?>

            <div class="row">
                <div class="col-sm-6">
                    <?= $form->field($model, 'trig_item_id')->widget(Select2::className(), [
                        'data' => Item::getList(),
                        'options' => [
                            'placeholder' => 'Выберите элемент ...',
                        ],
                    ]) ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model, 'trig_item_value')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <h2>Выполнить</h2>

            <?= $form->field($model, 'task_ids')->widget(Select2::className(), [
                'data' => \app\models\Task::getList(),
                'showToggleAll' => false,
                'options' => [
                    'placeholder' => 'Выберите задачи ...',
                    'multiple' => true,
                ],
            ]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить',
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
