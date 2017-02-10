<?php

/* @var $this yii\web\View */
/* @var $roomModels \app\models\Room[] */

use app\assets\PanelAsset;
use rmrevin\yii\fontawesome\FA;
use yii\helpers\Html;

PanelAsset::register($this);

$this->title = 'Панель Управления';
?>

<div class="linear-loader">
    <div class="indeterminate"></div>
</div>

<div id="loader">
    <div class="loader-icon">
        <div class="circle">
            <?= FA::i('home') ?>
        </div>
    </div>
    <div class="loader-brand">
        <span class="product-font"><span>Solomaha</span> Home</span>
    </div>
    <div class="loader-message">OFFLINE</div>
</div>

<main class="container control-panel">
    <section class="content-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </section>

    <div class="control-panel">

        <?php foreach ($roomModels as $room): ?>
            <div class="card panel-room" data-room-id="<?= $room->id ?>">
                <div class="card-header">
                    <h3 class="card-header-title"><?= $room->name ?></h3>
                </div>

                <div class="card-body">
                    <div class="row panel-items-switch">
                        <?php foreach ($room->getItems()->switches()->active()->all() as $item): ?>
                            <?= $this->render('_switch', [
                                'item' => $item,
                            ]) ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="row panel-items-variable">
                        <?php foreach ($room->getItems()->variables()->active()->all() as $item): ?>
                            <?= $this->render('_variable', [
                                'item' => $item,
                            ]) ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="row panel-items-rgb">
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
</main>
