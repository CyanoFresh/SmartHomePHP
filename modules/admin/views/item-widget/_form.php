<?php

/* @var $this yii\web\View */
/* @var $model app\models\ItemWidget */
/* @var $form yii\widgets\ActiveForm */

use app\models\Item;
use app\models\ItemWidget;
use app\models\Room;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

\app\assets\vendors\FontawesomeIconpickerAsset::register($this);

$this->registerJs("
$('.fontawesome-iconpicker-input').iconpicker({container: 'body', inputSearch: true,});
", \yii\web\View::POS_READY);
?>

<div class="item-widget-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'item_id')->widget(Select2::className(), [
                'data' => Item::getList(true),
                'pluginEvents' => [
                    'change' => "function(e) {
                        $.get({
                            url: '" . \yii\helpers\Url::to([
                                '/api/item/view',
                                'access-token' => Yii::$app->user->identity->api_key,
                            ]) . "&id=' + $('#itemwidget-item_id').val(),
                            success: function (data) {
                                if (data) {
                                    $('#itemwidget-name').val(data.name).change();
                                    
                                    var widgetType = data.type;
                                    var valueType;
                                    
                                    if (data.type == 20 || data.type == 21 || data.type == 22 || data.type == 25 || data.type == 26) {
                                        widgetType = 20;
                                        $('.field-itemwidget-value_type').fadeIn().removeClass('hidden');
                                    }
                                    
                                    if (data.type == 21) {
                                        valueType = 30;
                                    }
                                    
                                    if (data.type == 22) {
                                        valueType = 40;
                                    }
                                    
                                    if (data.type == 25) {
                                        valueType = 10;
                                    }
                                    
                                    if (data.type == 26) {
                                        valueType = 20;
                                    }
                                    
                                    $('#itemwidget-type').val(widgetType).change();
                                    $('#itemwidget-value_type').val(valueType).change();
                                }
                            }
                        });
                    }",
                ],
                'options' => [
                    'placeholder' => 'Выберите элемент ...',
                ],
            ]) ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'type')->widget(Select2::className(), [
                'data' => ItemWidget::getTypes(),
                'pluginEvents' => [
                    'change' => "function(e) {
                        if (parseInt($(this).val()) == 20) {
                            $('.field-itemwidget-value_type').fadeIn().removeClass('hidden');
                        } else {
                            $('.field-itemwidget-value_type').fadeOut().addClass('hidden');
                        }
                    }",
                ],
                'options' => [
                    'placeholder' => 'Выберите тип ...',
                ],
            ]) ?>

            <?= $form->field($model, 'value_type', ['options' => ['class' => 'form-group hidden']])->widget(Select2::className(), [
                'data' => ItemWidget::getValueTypes(),
                'options' => [
                    'placeholder' => 'Выберите тип значения ...',
                ],
            ]) ?>

            <?= $form->field($model, 'active')->checkbox() ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'icon')->textInput([
                'maxlength' => true,
                'class' => 'fontawesome-iconpicker-input form-control'
            ]) ?>

            <?= $form->field($model, 'room_id')->widget(Select2::className(), [
                'data' => Room::getList(),
                'options' => [
                    'placeholder' => 'Выберите комнату ...',
                ],
            ]) ?>

            <?= $form->field($model, 'html_class')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'sort_order')->input('number') ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
