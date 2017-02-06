<?php

/* @var $this yii\web\View */
/* @var $item \app\models\Item */

use rmrevin\yii\fontawesome\FA;

?>

<div class="col-lg-3 col-sm-4">
    <div class="panel-item panel-item-switch <?= $item->class ?>" data-item-id="<?= $item->id ?>">
        <div class="panel-item-switch-icon">
            <?= FA::i($item->icon) ?>
        </div>
        <div class="panel-item-switch-name">
            <?= $item->name ?>
        </div>
    </div>
</div>
