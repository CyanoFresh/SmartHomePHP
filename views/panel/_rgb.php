<?php

/* @var $this yii\web\View */
/* @var $item \app\models\Item */

?>

<div class="col-md-4 panel-item item-rgb" data-item-id="<?= $item->id ?>">
    <div class="info-box bg-<?= $item->bg ?> <?= $item->class ?>">
        <span class="info-box-icon"><i class="fa fa-<?= $item->icon ?>"></i></span>

        <div class="info-box-content">
            <div class="col-xs-4 border-right">
                <span class="info-box-text"><?= $item->name ?></span>
                <span class="info-box-number"><input type="text"
                                                     id="colorpicker-<?= $item->id ?>"
                                                     class="rgb-colorpicker"
                                                     data-item-id="<?= $item->id ?>"></span>
                <label>
                    <input type="checkbox" class="fade-checkbox" data-item-id="<?= $item->id ?>" checked>
                    Плавно
                </label>
            </div>
            <div class="col-xs-8">
                <span class="info-box-text">Режимы</span>
                <div class="rgb-modes-list">
                    <div class="rgb-mode" data-mode="rainbow">
                        <div class="rgb-mode-image rgb-mode-rainbow"></div>
                    </div>
                    <div class="rgb-mode" data-mode="breath">
                        <div class="rgb-mode-image rgb-mode-breath"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
