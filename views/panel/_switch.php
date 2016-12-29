<?php

/* @var $this yii\web\View */
/* @var $item \app\models\Item */

?>

<div class="col-md-3 col-sm-4 control-panel-item item-switch"
     data-item-id="<?= $item->id ?>">
    <div class="info-box bg-<?= $item->bg ?> <?= $item->class ?>">
        <div class="info-box-action material-switch">
            <input id="switch-for-item-<?= $item->id ?>" class="item-switch-checkbox"
                   type="checkbox" data-item-id="<?= $item->id ?>">
            <label for="switch-for-item-<?= $item->id ?>" class="label-default"></label>
        </div>

        <div class="info-box-content">
            <span class="info-box-text"><?= $item->name ?></span>
        </div>
        <!-- /.info-box-content -->
    </div>
</div>
