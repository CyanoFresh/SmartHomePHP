<?php
/* @var $this \yii\web\View */
/* @var $content string */
?>

<?php $this->beginContent('@app/views/layouts/base.php'); ?>

    <?= $this->render('_header.php') ?>

<!--    --><?//= $this->render('_left.php') ?>

    <?= $this->render('_content.php', ['content' => $content]) ?>

<?php $this->endContent(); ?>
