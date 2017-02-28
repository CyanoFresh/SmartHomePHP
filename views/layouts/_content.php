<?php

/* @var $this \yii\web\View */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$inCard = isset($this->params['in-card']) ? $this->params['in-card'] : true;
$tableCard = isset($this->params['table-card']) ? $this->params['table-card'] : false;
?>

<main class="content">
    <div class="container-fluid">
        <section class="content-header">
            <h1><?= Html::encode($this->title) ?></h1>

        </section>

        <section class="content-section">
            <?php if ($inCard): ?>
                <div class="card">
                    <?= $content ?>
                </div>
            <?php else: ?>
                <?= $content ?>
            <?php endif; ?>
        </section>
    </div>
</main>
