<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\MDThemeAsset;
use dmstr\web\AdminLteAsset;
use yii\helpers\Html;
use app\assets\AppAsset;

AppAsset::register($this);
AdminLteAsset::register($this);
MDThemeAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?= Html::csrfMetaTags() ?>

    <title><?= $this->title ?> - <?= Yii::$app->name ?></title>

    <meta name="application-name" content="<?= Yii::$app->name ?>">
    <meta name="theme-color" content="#009688">

    <link rel="publisher" href="https://plus.google.com/+AlexSolomaha21">
    <link rel="me" href="https://plus.google.com/+AlexSolomaha21" type="text/html">
    <link rel="me" href="mailto:cyanofresh@gmail.com">
    <link rel="me" href="sms:+380975300688">

    <?php $this->head() ?>
</head>

<body class="hold-transition">
<?php $this->beginBody() ?>

<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
