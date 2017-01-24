<?php

/* @var $this yii\web\View */
/* @var $model app\models\Trigger */
/* @var $form yii\widgets\ActiveForm */

use app\models\Board;
use app\models\Item;
use app\models\Task;
use app\models\Trigger;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\time\TimePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

if ($model->isNewRecord) {
    $model->active = true;
} elseif (is_string($model->weekdays)) {
    $model->weekdays = explode(', ', $model->weekdays);
}

?>

<div class="trigger-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-sm-3">
            <h2>Основное</h2>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'type')->widget(Select2::className(), [
                'data' => Trigger::getTypes(),
                'options' => [
                    'placeholder' => 'Выберите тип ...',
                ],
            ]) ?>

            <?= $form->field($model, 'active')->checkbox() ?>
        </div>
        <div class="col-sm-5">

            <h2>Условия</h2>

            <div class="row" id="type-10">
                <div class="col-sm-6">
                    <?= $form->field($model, 'time')->widget(TimePicker::className(), [
                        'pluginOptions' => [
                            'showMeridian' => false,
                            'defaultTime' => false,
                        ],
                    ]) ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model, 'weekdays')->widget(Select2::className(), [
                        'data' => Trigger::getWeekDays(),
                        'options' => [
                            'multiple' => true,
                            'placeholder' => 'Выберите дни ...',
                        ],
                    ]) ?>
                </div>
            </div>

            <p>или</p>

            <div id="type-10">
                <?= $form->field($model, 'date')->widget(DateControl::className(), [
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
            </div>

            <p>или</p>

            <div class="row type-10 type-20">
                <div class="col-sm-6">
                    <?= $form->field($model, 'item_id')->widget(Select2::className(), [
                        'data' => Item::getList(false, true),
                        'options' => [
                            'placeholder' => 'Выберите элемент ...',
                        ],
                    ]) ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model, 'item_value')->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <p>или</p>

            <div class="row type-10 type-20">
                <div class="col-sm-6">
                    <?= $form->field($model, 'board_id')->widget(Select2::className(), [
                        'data' => Board::getList(),
                        'options' => [
                            'placeholder' => 'Выберите устройство ...',
                        ],
                    ]) ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model, 'connection_value')->widget(Select2::className(), [
                        'data' => Trigger::getConnectionValues(),
                        'options' => [
                            'placeholder' => 'Выберите значение ...',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <h2>Выполнить</h2>

            <?= $form->field($model, 'task_ids')->widget(Select2::className(), [
                'data' => Task::getList(),
                'showToggleAll' => false,
                'options' => [
                    'placeholder' => 'Выберите задачи ...',
                    'multiple' => true,
                ],
            ]) ?>
        </div>
    </div>

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
