<?php

/* @var $this yii\web\View */

use app\models\Item;
use app\models\Room;

$this->title = 'Панель Управления';
$this->params['not-boxed'] = true;

?>

<div class="control-panel">
    <?php foreach (Room::find()->all() as $room): ?>
        <div class="box control-panel-room box-<?= $room->bg ?>" data-room-id="<?= $room->id ?>">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $room->name ?></h3>
            </div>

            <div class="box-body control-panel-items">
                <div class="row items-variable">
                    <?php foreach ($room->getItems()->variables()->all() as $item): ?>
                        <div class="col-md-3 col-sm-6 col-xs-12 control-panel-item item-variable"
                             data-item-id="<?= $item->id ?>">
                            <div class="info-box bg-<?= $item->bg ?> <?= $item->class ?>">
                                <span class="info-box-icon"><i class="fa fa-<?= $item->icon ?>"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text"><?= $item->name ?></span>
                                    <span class="info-box-number item-value"></span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="row items-switch">
                    <?php foreach ($room->getItems()->switches()->all() as $item): ?>
                        <div class="col-md-2 col-sm-4 col-xs-12 control-panel-item item-switch"
                             data-item-id="<?= $item->id ?>">
                            <div class="info-box bg-<?= $item->bg ?> <?= $item->class ?>">
                                <span class="info-box-icon"><i class="fa fa-<?= $item->icon ?>"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text"><?= $item->name ?></span>
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
