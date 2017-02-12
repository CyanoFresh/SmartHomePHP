<?php

/* @var $this \yii\web\View */

use rmrevin\yii\fontawesome\FA;
use yii\bootstrap\Nav;

$drawerOpen = true;

Yii::$app->request->cookies->has('drawer-hidden')

?>

<aside id="drawer" class="drawer">
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
