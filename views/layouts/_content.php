<?php

/* @var $this \yii\web\View */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

?>

<main class="content">
    <div class="container-fluid">
        <section class="content-header">
            <h1><?= Html::encode($this->title) ?></h1>

            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
        </section>

        <section>
            <?= $content ?>
        </section>

        <footer class="main-footer">
            <span class="product-font">
                <span>Solomaha</span> Home by <a href="https://solomaha.com" target="_blank">Alex Solomaha</a>
            </span>
        </footer>

    </div>
</main>
