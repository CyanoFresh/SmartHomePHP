<?php

/* @var $this yii\web\View */
/* @var $item \app\models\Item */

use rmrevin\yii\fontawesome\FA;

?>

<div class="col-md-2 col-sm-3">
    <div class="panel-item panel-item-variable bg-<?= $item->bg ?> withripple" data-item-id="<?= $item->id ?>">
        <div class="item-variable-icon">
            <?= FA::i($item->icon) ?>
        </div>
        <div class="item-variable-value item-value">0</div>
    </div>
</div>
