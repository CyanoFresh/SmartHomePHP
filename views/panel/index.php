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
                    <?php foreach ($room->getItems()->variables()->active()->all() as $item): ?>
                        <?= $this->render('_variable', [
                            'item' => $item,
                        ]) ?>
                    <?php endforeach; ?>
                </div>
                <div class="row items-switch">
                    <?php foreach ($room->getItems()->switches()->active()->all() as $item): ?>
                        <?= $this->render('_switch', [
                            'item' => $item,
                        ]) ?>
                    <?php endforeach; ?>
                </div>
                <div class="row items-rgb">
                    <?php foreach ($room->getItems()->rgb()->all() as $item): ?>
                        <?= $this->render('_rgb', [
                            'item' => $item,
                        ]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    <?php endforeach; ?>
</div>
