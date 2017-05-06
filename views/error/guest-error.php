<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>

<div class="container">
    <div class="error-page-logo">
        <a href="<?= \yii\helpers\Url::home() ?>" class="product-font">
            <span>Solomaha</span> Home
        </a>
    </div>

    <h1><?= $this->title ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>
</div>
