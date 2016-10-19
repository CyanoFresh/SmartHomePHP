<?php

use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;

?>
<div class="content-wrapper">
    <section class="content-header">
        <?php if (isset($this->blocks['content-header'])): ?>
            <h1><?= $this->blocks['content-header'] ?></h1>
        <?php else: ?>
            <h1>
                <?php
                if ($this->title !== null) {
                    echo Html::encode($this->title);
                } else {
                    echo Inflector::camel2words(
                        Inflector::id2camel($this->context->module->id)
                    );
                    echo ($this->context->module->id !== Yii::$app->id) ? '<small>Module</small>' : '';
                } ?>
            </h1>
        <?php endif; ?>

        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
    </section>

    <section class="content">
        <?= Alert::widget() ?>

        <?php if (!isset($this->params['not-boxed']) or !$this->params['not-boxed']): ?>
            <div class="box">
                <div class="box-body">
                    <?= $content ?>
                </div>
            </div>
        <?php else: ?>
            <?= $content ?>
        <?php endif; ?>
    </section>
</div>

<footer class="main-footer">
    <span class="product-font">
        <span>Smart</span> Home by <a href="https://solomaha.com" target="_blank">Alex Solomaha</a>
    </span>
</footer>

