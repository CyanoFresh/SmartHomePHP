<?php

/* @var $this yii\web\View */
/* @var $widget \app\models\ItemWidget */

?>

<div class="col-lg-3 col-sm-4 col-xs-4">
    <div class="panel-item panel-item-variable <?= $widget->html_class ?> withripple"
         title="<?= $widget->getName() ?>"
         data-item-id="<?= $widget->item_id ?>"
         data-item-type="<?= $widget->item->type ?>">
        <div class="item-variable-icon">
            <i class="fa <?= $widget->icon ?>"></i>
        </div>
        <div class="item-variable-value item-value"><?= $widget->item->getDefaultNAValue() ?></div>
    </div>
</div>
