<?php

/* @var $this \yii\web\View */
/* @var $content string */

use dmstr\helpers\AdminLteHelper;
use dmstr\web\AdminLteAsset;
use yii\helpers\Html;
use app\assets\AppAsset;

AppAsset::register($this);
AdminLteAsset::register($this);

if (isset($this->params['body-class'])) {
    $bodyClass = $this->params['body-class'];
} else {
    $bodyClass = null;
}
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
    <meta name="theme-color" content="#605CA8">

    <link rel="publisher" href="https://plus.google.com/+AlexSolomaha21">
    <link rel="me" href="https://plus.google.com/+AlexSolomaha21" type="text/html">
    <link rel="me" href="mailto:cyanofresh@gmail.com">
    <link rel="me" href="sms:+380975300688">

    <?php $this->head() ?>
</head>

<body class="hold-transition <?= AdminLteHelper::skinClass() ?> <?= $bodyClass ?> sidebar-mini">
<?php $this->beginBody() ?>

<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
