<?php

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */

use app\models\Room;
use app\models\User;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$randomApiKey = false;

if ($model->isNewRecord) {
    $randomApiKey = md5(time());
}

?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput() ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'email')->input('email') ?>

    <?= $form->field($model, 'name')->textInput() ?>

    <?= $form->field($model, 'avatar')->textInput() ?>

    <?= $form->field($model, 'status')->dropDownList(User::getStatuses()) ?>

    <?= $form->field($model, 'group')->dropDownList(User::getGroups()) ?>

    <?= $form->field($model, 'room_id')->widget(Select2::className(), [
        'data' => Room::getList(),
        'options' => [
            'placeholder' => 'Выберите комнату ...',
        ],
    ]) ?>

    <?= $form->field($model, 'api_key')->textInput()->hint($randomApiKey) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
