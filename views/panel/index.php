<?php

/* @var $this yii\web\View */

use app\assets\WSClientAsset;
use app\models\Item;
use app\models\Room;
use yii\web\View;

$this->title = 'Панель Управления';
$this->params['not-boxed'] = true;

WSClientAsset::register($this);
?>

<div class="loader" id="loader">
    <div class="loader-animation">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="loader-text text-danger"></div>
</div>

<div class="control-panel">

    <?php foreach (Room::find()->all() as $room): ?>
        <div class="box control-panel-room box-<?= $room->bg ?>" data-room-id="<?= $room->id ?>">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $room->name ?></h3>
            </div>

            <div class="box-body control-panel-items">
                <div class="row items-variable">
                    <?php foreach ($room->getItems()->variables()->all() as $item): ?>
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
                    <?php endforeach; ?>
                </div>
                <div class="row items-switch">
                    <?php foreach ($room->getItems()->switches()->all() as $item): ?>
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
                    <?php endforeach; ?>
                </div>
                <div class="row items-rgb">
                    <?php foreach ($room->getItems()->rgb()->all() as $item): ?>
                        <div class="col-md-3 col-sm-4 control-panel-item item-rgb"
                             data-item-id="<?= $item->id ?>">
                            <div class="info-box bg-<?= $item->bg ?> <?= $item->class ?>">
                                <span class="info-box-icon"><i class="fa fa-<?= $item->icon ?>"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text"><?= $item->name ?></span>
                                    <input type="text" id="colorpicker-<?= $item->id ?>" class="rgb-colorpicker" data-item-id="<?= $item->id ?>">
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    <?php endforeach; ?>
</div>
