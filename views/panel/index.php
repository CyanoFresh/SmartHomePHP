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

<main class="content">
    <div class="container-fluid">
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

        <div class="control-panel">
            <section class="content-header">
                <h1><?= Html::encode($this->title) ?></h1>
            </section>

            <?php foreach ($roomModels as $room): ?>
                <div class="card panel-room" data-room-id="<?= $room->id ?>">
                    <div class="card-header">
                        <h3 class="card-header-title"><?= $room->name ?></h3>
                    </div>

                    <div class="card-body">
                        <div class="row panel-items-variable">
                            <?php foreach ($room->getItems()->variables()->active()->all() as $item): ?>
                                <?= $this->render('_variable', [
                                    'item' => $item,
                                ]) ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="row panel-items-switch">
                            <?php foreach ($room->getItems()->switches()->active()->all() as $item): ?>
                                <?= $this->render('_switch', [
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
    </div>
</main>

<script id="rgb-item-widget-popover-content" type="text/x-handlebars-template">
    <div class="rgb-widget-popover-content" data-item-id="{{item_id}}">
        <ul class="nav nav-tabs nav-justified">
            <li class="active">
                <a href="#rgb-widget-static" class="rgb-widget-mode rgb-widget-mode-static" data-toggle="tab"
                   aria-expanded="true">
                    Static
                </a>
            </li>
            <li>
                <a href="#rgb-widget-wave" class="rgb-widget-mode rgb-widget-mode-wave" data-toggle="tab"
                   aria-expanded="false">
                    Wave
                </a>
            </li>
            <li>
                <a href="#rgb-widget-fade" class="rgb-widget-mode rgb-widget-mode-fade" data-toggle="tab"
                   aria-expanded="false">
                    Fade
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade in active" id="rgb-widget-static">
                <form class="rgb-widget-form rgb-widget-form-static">
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="colorpicker-{{item_id}}" class="hidden">Цвет</label>
                            <input type="text"
                                   class="rgb-widget-colorpicker rgb-widget-colorpicker-static"
                                   data-item-id="{{item_id}}"
                                   id="colorpicker-{{item_id}}">
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="rgb-widget-static-fade-time">Время перехода (мс)</label>
                                <input type="number" min="0" step="500" class="form-control" id="rgb-widget-static-fade-time"
                                       value="<?= Yii::$app->params['items']['rgb']['fade-time'] ?>">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade" id="rgb-widget-wave">
                <form class="rgb-widget-form rgb-widget-form-wave">
                    <div class="form-group">
                        <label for="rgb-widget-wave-fade-time">Время перехода (мс)</label>
                        <input type="number" min="0" step="500" class="form-control" id="rgb-widget-wave-fade-time"
                               value="<?= Yii::$app->params['items']['rgb']['fade-time'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="rgb-widget-wave-color-time">Время цвета (мс)</label>
                        <input type="number" min="0" step="500" class="form-control" id="rgb-widget-wave-color-time"
                               value="<?= Yii::$app->params['items']['rgb']['color-time'] ?>">
                    </div>
                    <button class="btn btn-primary btn-save-times" data-mode="wave">Применить</button>
                </form>
            </div>
            <div class="tab-pane fade" id="rgb-widget-fade">
                <form class="rgb-widget-form rgb-widget-form-fade">
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="fade-colorpicker-{{item_id}}" class="hidden">Цвет</label>
                            <input type="text"
                                   class="rgb-widget-colorpicker rgb-widget-colorpicker-fade"
                                   data-item-id="{{item_id}}"
                                   id="fade-colorpicker-{{item_id}}">
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="rgb-widget-fade-fade-time">Время перехода (мс)</label>
                                <input type="number" min="0" step="500" class="form-control" id="rgb-widget-fade-fade-time"
                                       value="<?= Yii::$app->params['items']['rgb']['fade-time'] ?>">
                            </div>
                            <div class="form-group">
                                <label for="rgb-widget-fade-color-time">Время цвета (мс)</label>
                                <input type="number" min="0" step="500" class="form-control" id="rgb-widget-fade-color-time"
                                       value="<?= Yii::$app->params['items']['rgb']['color-time'] ?>">
                            </div>
                            <button class="btn btn-primary btn-save-times" data-mode="fade">Применить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</script>
