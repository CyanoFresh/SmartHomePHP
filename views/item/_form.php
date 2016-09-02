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

?>

<div class="item-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'icon')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bg')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'class')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'board_id')->dropDownList(
        ArrayHelper::map(Board::find()->all(), 'id', 'name')
    ) ?>

    <?= $form->field($model, 'pin')->input('number') ?>

    <?= $form->field($model, 'type')->dropDownList(Item::getTypesArray()) ?>

    <?= $form->field($model, 'update_interval')->textInput() ?>

    <?= $form->field($model, 'save_history_interval')->input('number') ?>

    <?= $form->field($model, 'room_id')->dropDownList(
        ArrayHelper::map(Room::find()->all(), 'id', 'name')
    ) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sort_order')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить',
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
