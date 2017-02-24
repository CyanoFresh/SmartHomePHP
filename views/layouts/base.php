<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\MDThemeAsset;
use yii\helpers\Html;
use app\assets\AppAsset;
use yii\helpers\Url;

AppAsset::register($this);
MDThemeAsset::register($this);

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

    <meta name="application-name" content="<?= Yii::$app->name ?>">
    <meta name="theme-color" content="#009688">

    <link rel="icon" type="image/png" href="<?= Url::home(true) ?>favicon.png">
    <link rel="icon" type="image/x-icon" href="<?= Url::home(true) ?>favicon.ico">

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
