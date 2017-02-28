<?php

/* @var $this \yii\web\View */

use rmrevin\yii\fontawesome\FA;
use yii\bootstrap\Nav;

?>

<aside id="drawer" class="drawer">
    <div class="user-block">
        <div class="user-avatar">
            <img src="<?= Yii::$app->user->identity->getAvatar(40) ?>">
        </div>
        <div class="user-info">
            <div class="user-info-name">
                <?= Yii::$app->user->identity->username ?>
            </div>
        </div>
        <div class="user-caret"><?= FA::i('chevron-down') ?></div>
    </div>
    <?= Nav::widget([
        'items' => [
            [
                'label' => FA::i('sliders fa-fw') . 'Панель Управления',
                'url' => ['/panel/index'],
            ],
            [
                'label' => FA::i('history fa-fw') . 'История',
                'url' => ['/history/index'],
            ],
            '<li class="divider"></li>',
            [
                'label' => FA::i('toggle-on fa-fw') . 'Элементы',
                'url' => ['/admin/item/index'],
            ],
            [
                'label' => FA::i('hdd-o fa-fw') . 'Устройства',
                'url' => ['/admin/board/index'],
            ],
            [
                'label' => FA::i('code-fork fa-fw') . 'События',
                'url' => ['/admin/event/index'],
            ],
            [
                'label' => FA::i('feed fa-fw') . 'Триггеры',
                'url' => ['/admin/trigger/index'],
            ],
            [
                'label' => FA::i('check fa-fw') . 'Задачи',
                'url' => ['/admin/task/index'],
            ],
            [
                'label' => FA::i('folder-open fa-fw') . 'Комнаты',
                'url' => ['/admin/room/index'],
            ],
            [
                'label' => FA::i('cog fa-fw') . 'Настройки',
                'url' => ['/admin/setting/index'],
            ],
            [
                'label' => FA::i('users fa-fw') . 'Пользователи',
                'url' => ['/admin/user/index'],
            ],
            '<li class="divider"></li>',
            [
                'label' => FA::i('user fa-fw') . 'Профиль',
                'url' => ['/profile/index'],
            ],
            [
                'label' => FA::i('sign-out fa-fw') . 'Выйти',
                'url' => ['/auth/logout'],
                'linkOptions' => [
                    'data-method' => 'post',
                ],
            ],
        ],
        'encodeLabels' => false,
        'options' => [
            'class' => 'drawer-menu nav-stacked',
        ],
    ]); ?>
</aside>
