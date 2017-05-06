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
    $model->enable_log = true;
}
?>

<div class="item-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
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

            <?= $form->field($model, 'default_value')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'update_interval')->input('number') ?>

            <?= $form->field($model, 'save_history_interval')->input('number') ?>
        </div>
    </div>

    <?= $form->field($model, 'enable_log')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить',
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
