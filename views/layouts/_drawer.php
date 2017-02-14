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
                'label' => FA::i('sliders') . 'Панель Управления',
                'url' => ['/panel/index'],
            ],
            [
                'label' => FA::i('history') . 'История',
                'url' => ['/history/index'],
            ],
            [
                'label' => FA::i('user') . 'Профиль',
                'url' => ['/profile/index'],
            ],
            [
                'label' => FA::i('cog') . 'Настройки',
                'url' => ['/admin/setting/index'],
            ],
            [
                'label' => FA::i('toggle-on') . 'Элементы',
                'url' => ['/admin/setting/index'],
            ],
            [
                'label' => FA::i('hdd-o') . 'Устройства',
                'url' => ['/admin/setting/index'],
            ],
            [
                'label' => FA::i('code-fork') . 'События',
                'url' => ['/admin/setting/index'],
            ],
            [
                'label' => FA::i('feed') . 'Триггеры',
                'url' => ['/admin/setting/index'],
            ],
            [
                'label' => FA::i('check') . 'Задачи',
                'url' => ['/admin/setting/index'],
            ],
            [
                'label' => FA::i('folder-open') . 'Комнаты',
                'url' => ['/admin/setting/index'],
            ],
            [
                'label' => FA::i('users') . 'Пользователи',
                'url' => ['/admin/setting/index'],
            ],
        ],
        'encodeLabels' => false,
        'options' => [
            'class' => 'drawer-menu nav-stacked',
        ],
    ]); ?>
</aside>
