<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\MDThemeAsset;
use yii\helpers\Html;
use app\assets\AppAsset;

AppAsset::register($this);

$bodyClass = isset($this->params['bodyClass']) ? $this->params['bodyClass'] : '';
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

    <link rel="apple-touch-icon" sizes="180x180" href="<?= Yii::$app->homeUrl ?>img/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="<?= Yii::$app->homeUrl ?>img/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="<?= Yii::$app->homeUrl ?>img/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="<?= Yii::$app->homeUrl ?>manifest.json">
    <link rel="mask-icon" href="<?= Yii::$app->homeUrl ?>img/safari-pinned-tab.svg" color="#5bbad5">

    <meta name="application-name" content="<?= Yii::$app->name ?>">
    <meta name="theme-color" content="#009688">

    <link rel="publisher" href="https://plus.google.com/+AlexSolomaha21">
    <link rel="me" href="https://plus.google.com/+AlexSolomaha21" type="text/html">
    <link rel="me" href="mailto:cyanofresh@gmail.com">
    <link rel="me" href="sms:+380975300688">

    <?php $this->head() ?>
</head>

<body class="<?= $bodyClass ?>">
<?php $this->beginBody() ?>

<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
