<?php

/* @var $this yii\web\View */
/* @var $item \app\models\Item */

?>

<div class="col-md-2 col-sm-3">
    <div class="panel-item panel-item-switch <?= $item->class ?>" data-item-id="<?= $item->id ?>">
        <div class="panel-item-switch-icon">
            <i class="fa fa-lightbulb-o"></i>
        </div>
        <div class="panel-item-switch-name">
            <?= $item->name ?>
        </div>
    </div>
</div>
