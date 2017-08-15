<?php

/* @var $this yii\web\View */
/* @var $widget \app\models\ItemWidget */

?>

<div class="col-lg-4 col-md-6 col-xs-6">
    <div class="panel-item panel-item-plant <?= $widget->html_class ?>" data-item-id="<?= $widget->item_id ?>">
        <div class="panel-item-plant-name">
            <?= $widget->getName() ?>
        </div>
        <div class="panel-item-plant-row">
            <div class="panel-item-plant-icon">
                <i class="fa <?= $widget->icon ?> fa-fw"></i>
            </div>
            <div class="panel-item-plant-soil-moisture">0 %</div>
        </div>
        <div class="panel-item-plant-actions">
            <a href="#" class="btn btn-default btn-block btn-plant-do-watering">
                <i class="fa fa-tint"></i> Полить
            </a>
        </div>
    </div>
</div>
