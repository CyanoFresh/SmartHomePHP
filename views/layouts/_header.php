<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;

?>

<header class="main-header">

    <nav class="navbar navbar-inverse navbar-static-top" role="navigation">

        <div class="navbar-title">
            <a href="<?= Url::home() ?>" class="product-font"><span>Solomaha</span> Home</a>
        </div>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">

                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <!--                        <span class="label label-warning">10</span>-->
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">You have 10 notifications</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu">
                                <li>
                                    <a href="#">
                                        <i class="fa fa-users text-aqua"></i> 5 new members joined today
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="footer"><a href="#">View all</a></li>
                    </ul>
                </li>

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= Yii::$app->user->identity->getAvatar() ?>" class="user-image" alt="User Image">
                        <span class="hidden-xs"><?= Yii::$app->user->identity->username ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <div class="row">
                                <div class="col-xs-4">
                                    <img src="<?= Yii::$app->user->identity->getAvatar(200) ?>" class="img-circle img-responsive"
                                         alt="User Image">
                                </div>
                                <div class="col-xs-8">
                                    <h4><?= Yii::$app->user->identity->username ?></h4>

                                    <small>
                                        Зарегистрирован <?= Yii::$app->formatter->asDate(Yii::$app->user->identity->created_at) ?>
                                    </small>
                                </div>
                            </div>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="<?= Url::to(['/user/view', 'id' => Yii::$app->user->id]) ?>"
                                   class="btn btn-default">
                                    Профиль
                                </a>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    'Выйти',
                                    ['/auth/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-primary']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </nav>
</header>
