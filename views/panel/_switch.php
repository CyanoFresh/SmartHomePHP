<?php

/* @var $this yii\web\View */
/* @var $widget \app\models\ItemWidget */

?>

<div class="col-lg-3 col-md-4 col-sm-6">
    <div class="panel-item panel-item-switch withripple <?= $widget->html_class ?>" data-item-id="<?= $widget->item_id ?>">
        <div class="panel-item-switch-icon">
            <i class="fa <?= $widget->icon ?>"></i>
        </div>
        <div class="panel-item-switch-name">
            <?= $widget->getName() ?>
        </div>
    </div>
</div>
