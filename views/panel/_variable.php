<?php

/* @var $this yii\web\View */
/* @var $item \app\models\Item */

use rmrevin\yii\fontawesome\FA;

?>

<div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
    <div class="panel-item panel-item-variable bg-<?= $item->bg ?> withripple"
         title="<?= $item->name ?>"
         data-item-id="<?= $item->id ?>">
        <div class="item-variable-icon">
            <?= FA::i($item->icon) ?>
        </div>
        <div class="item-variable-value item-value"><?= $item->getDefaultNAValue() ?></div>
    </div>
</div>
