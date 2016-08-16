<?php

/* @var $this \yii\web\View */
/* @var $content string */

$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
?>

<?php $this->beginContent('@app/views/layouts/base.php'); ?>

<div class="wrapper">

    <?= $this->render('_header.php', ['directoryAsset' => $directoryAsset]) ?>

    <?= $this->render('_left.php', ['directoryAsset' => $directoryAsset]) ?>

    <?= $this->render('_content.php', ['content' => $content, 'directoryAsset' => $directoryAsset]) ?>

</div>

<?php $this->endContent(); ?>
