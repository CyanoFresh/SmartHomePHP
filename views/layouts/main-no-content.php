<?php
/* @var $this \yii\web\View */
/* @var $content string */
?>

<?php $this->beginContent('@app/views/layouts/base.php'); ?>

    <?= $this->render('_header.php') ?>

    <?= $content ?>

<?php $this->endContent(); ?>
