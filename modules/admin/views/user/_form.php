<?php

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */

use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

if ($model->isNewRecord) {
    $model->api_key = md5(time());
}

?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput() ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'email')->input('email') ?>

    <?= $form->field($model, 'status')->dropDownList(User::getStatuses()) ?>

    <?= $form->field($model, 'group')->dropDownList(User::getGroups()) ?>

    <?= $form->field($model, 'api_key')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
