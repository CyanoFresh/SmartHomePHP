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

            <div class="row masonry-container">
                <?php foreach ($roomModels as $room): ?>
                    <div class="col-md-6 col-sm-12 masonry-item">
                        <div class="card panel-room" data-room-id="<?= $room->id ?>">
                            <div class="card-header">
                                <h3 class="card-header-title"><?= $room->name ?></h3>
                            </div>

                            <div class="card-body">
                                <div class="row panel-items-variable">
                                    <?php foreach ($room->getItemWidgets()->variables()->active()->all() as $widget): ?>
                                        <?= $this->render('_variable', [
                                            'widget' => $widget,
                                        ]) ?>
                                    <?php endforeach; ?>
                                </div>
                                <div class="row panel-items-switch">
                                    <?php foreach ($room->getItemWidgets()->switches()->active()->all() as $widget): ?>
                                        <?= $this->render('_switch', [
                                            'widget' => $widget,
                                        ]) ?>
                                    <?php endforeach; ?>
                                </div>
                                <div class="row panel-items-rgb">
                                    <?php foreach ($room->getItemWidgets()->rgb()->active()->all() as $widget): ?>
                                        <?= $this->render('_rgb', [
                                            'widget' => $widget,
                                        ]) ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
                                <input type="number" min="0" step="500" class="form-control"
                                       id="rgb-widget-static-fade-time"
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
                                <input type="number" min="0" step="500" class="form-control"
                                       id="rgb-widget-fade-fade-time"
                                       value="<?= Yii::$app->params['items']['rgb']['fade-time'] ?>">
                            </div>
                            <div class="form-group">
                                <label for="rgb-widget-fade-color-time">Время цвета (мс)</label>
                                <input type="number" min="0" step="500" class="form-control"
                                       id="rgb-widget-fade-color-time"
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

<!-- Item Chart Modal -->
<div class="modal fade" id="item-chart-modal" tabindex="-1" role="dialog" aria-labelledby="item-chart-modal-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="item-chart-modal-label">График "<span class="item-chart-name"></span>"</h4>
            </div>
            <div class="modal-body">
                <canvas id="item-chart"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Debug Modal -->
<div class="modal fade" id="debug-modal" tabindex="-1" role="dialog" aria-labelledby="debug-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="debug-modal-label">Debug</h4>
            </div>
            <div class="modal-body">
                <div id="debug-messages"></div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="sr-only" for="exampleInputEmail3">Плата</label>
                            <?= \kartik\select2\Select2::widget([
                                'id' => 'send-board-board_id',
                                'name' => 'dsfsdf',
                                'data' => \app\models\Board::getList(),
                            ]) ?>
                        </div>
                    </div>

                    <div class="col-sm-5">
                        <textarea id="send-board-message" class="form-control" placeholder="Сообщение"></textarea>
                    </div>

                    <div class="col-sm-3">
                        <a href="#" class="btn btn-primary" id="send-board">Отправить</a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
