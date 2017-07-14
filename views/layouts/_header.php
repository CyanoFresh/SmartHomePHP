<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <a href="#" class="navbar-toggle-drawer">
                <div class="navbar-toggle-drawer-icon">
                    <span class="toggle-bar"></span>
                    <span class="toggle-bar"></span>
                    <span class="toggle-bar"></span>
                </div>
            </a>

            <a class="navbar-brand product-font" href="<?= Url::home() ?>"><span>Solomaha</span> Home</a>
        </div>

        <div class="navbar-breadcrumbs hidden-xs">

            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                'homeLink' => false,
            ]) ?>

        </div>

        <ul class="nav navbar-nav navbar-right hidden-xs">
            <li>
                <a href="#" data-toggle="modal" data-target="#debug-modal"><i class="fa fa-bug"></i></a>
            </li>
            <li class="dropdown notifications-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                   aria-expanded="false"><i class="fa fa-bell"></i></a>
                <ul class="dropdown-menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                </ul>
            </li>
        </ul>
    </div><!-- /.container-fluid -->
</nav>
