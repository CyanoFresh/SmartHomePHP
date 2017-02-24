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

        <section class="card">
            <?= $content ?>
        </section>
    </div>
</main>
