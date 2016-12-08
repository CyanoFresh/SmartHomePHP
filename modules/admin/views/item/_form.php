<?php

/* @var $this yii\web\View */
/* @var $model app\models\Item */
/* @var $form yii\widgets\ActiveForm */

use app\models\Board;
use app\models\Item;
use app\models\Room;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

if ($model->isNewRecord) {
    $model->active = true;
    $model->enable_log = true;
}
?>

<div class="item-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <h2>Основные настройки</h2>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'type')->dropDownList(Item::getTypesArray(), [
                'prompt' => '--- выберите тип ---',
            ]) ?>

            <?= $form->field($model, 'board_id')->dropDownList(Board::getList(), [
                'prompt' => '--- выберите плату ---',
            ]) ?>

            <div class="row">
                <div class="col-sm-6">
                    <?= $form->field($model, 'pin')->input('number') ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <?= $form->field($model, 'update_interval')->input('number') ?>

            <?= $form->field($model, 'save_history_interval')->input('number') ?>
        </div>
        <div class="col-md-6">
            <h2>Сниппет</h2>

            <?= $form->field($model, 'icon')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'bg')->dropDownList([
                'light-blue' => 'Светло-синий',
                'aqua' => 'Бирюзовый',
                'green' => 'Зеленый',
                'yellow' => 'Желтый',
                'red' => 'Красный',
                'gray' => 'Серый',
                'navy' => 'Navy',
                'teal' => 'Teal',
                'purple' => 'Фиолетовый',
                'orange' => 'Оранжевый',
                'maroon' => 'Бордовый',
                'black' => 'Черный',
            ]) ?>

            <?= $form->field($model, 'class')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'room_id')->dropDownList(
                ArrayHelper::map(Room::find()->all(), 'id', 'name')
            ) ?>

            <?= $form->field($model, 'sort_order')->input('number') ?>
        </div>
    </div>

    <?= $form->field($model, 'active')->checkbox() ?>

    <?= $form->field($model, 'enable_log')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить',
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
