<?php

/* @var $this yii\web\View */
/* @var $model History */

use app\models\History;
use app\models\Item;

?>

<div class="panel panel-defaul">
    <div class="panel-heading">
        <h4>
        <?php if ($model->type === History::TYPE_BOARD_CONNECTION): ?>

            Плата <b><?= $model->board->name ?></b>

            <b><?php if ($model->value == 1): ?>
                подключилась
            <?php else: ?>
                отключилась
            <?php endif ?></b>

        <?php elseif ($model->type === History::TYPE_USER_CONNECTION): ?>

            Пользователь <b><?= $model->user->username ?></b>

            <b><?php if ($model->value == 1): ?>
                подключился
            <?php else: ?>
                отключился
            <?php endif ?></b>

        <?php elseif ($model->type === History::TYPE_USER_ACTION): ?>

            Пользователь <b><?= $model->user->username ?></b>

            <b><?php if ($model->item->type === Item::TYPE_SWITCH): ?>

                <?php if ($model->value == 1): ?>
                    включил
                <?php else: ?>
                    выключил
                <?php endif ?>

                <?= $model->item->name ?>

            <?php elseif ($model->item->type === Item::TYPE_RGB): ?></b>

                установил значение <b><?= $model->value ?></b> на RGB <b><?= $model->item->name ?></b> в комнате <b><?= $model->item->board->name ?></b>

            <?php endif ?>
        <?php elseif ($model->type === History::TYPE_ITEM_VALUE): ?>

            <?php if ($model->item->type === Item::TYPE_SWITCH): ?>

                <b><?= $model->item->name ?></b> был

                <?php if ($model->value == 1): ?>
                    ВКЛ
                <?php else: ?>
                    ВЫКЛ
                <?php endif ?>

                в комнате <b><?= $model->item->board->name ?></b>

            <?php elseif ($model->item->type === Item::TYPE_VARIABLE_TEMPERATURE): ?>

                Значение температуры <b><?= $model->item->name ?></b> было <b><?= $model->value ?> °С</b> в комнате <b><?= $model->item->board->name ?></b>

            <?php elseif ($model->item->type === Item::TYPE_VARIABLE_HUMIDITY): ?>

                Значение влажности <b><?= $model->item->name ?></b> было <b><?= $model->value ?> %</b> в комнате <b><?= $model->item->board->name ?></b>

            <?php elseif ($model->item->type === Item::TYPE_VARIABLE_BOOLEAN): ?>

                Значение элемента <b><?= $model->item->name ?></b> было <b><?= Yii::$app->formatter->asBoolean($model->value) ?></b> в комнате <b><?= $model->item->board->name ?></b>

            <?php elseif ($model->item->type === Item::TYPE_VARIABLE_BOOLEAN_DOOR): ?>

                Дверь <b><?= $model->item->name ?></b> была

                <b><?php if ($model->value == 1): ?>
                    открыта
                <?php else: ?>
                    закрыта
                <?php endif ?></b> в комнате <b><?= $model->item->board->name ?></b>

            <?php else: ?>

                Значение элемента <b><?= $model->item->name ?></b> было <b><?= $model->value ?></b> в комнате <b><?= $model->item->board->name ?></b>

            <?php endif ?>
        <?php endif; ?>

        </h4>

        <div class="text-muted">
            <?= Yii::$app->formatter->asDatetime($model->commited_at) ?>
            •
            <?= $model->getTypeLabel() ?>
        </div>
    </div>
</div>
