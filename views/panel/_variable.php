<?php

/* @var $this yii\web\View */
/* @var $item \app\models\Item */

use app\models\Item;

?>

<div class="col-md-3 col-sm-4 control-panel-item item-variable"
     data-item-id="<?= $item->id ?>">
    <div class="info-box bg-<?= $item->bg ?> <?= $item->class ?>">
        <span class="info-box-icon"><i class="fa fa-<?= $item->icon ?>"></i></span>

        <div class="info-box-content">
            <span class="info-box-text"><?= $item->name ?></span>
            <span
                class="info-box-number item-value <?= ($item->type === Item::TYPE_VARIABLE_TEMPERATURE or $item->type === Item::TYPE_VARIABLE_HUMIDITY) ? 'temperature' : '' ?>">
                                        НЕИЗВЕСТНО
                                    </span>
        </div>
        <!-- /.info-box-content -->
    </div>
</div>
