<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= Yii::$app->user->identity->getAvatar() ?>" class="img-circle" alt="<?= Yii::$app->user->identity->username ?> Avatar">
            </div>
            <div class="pull-left info">
                <p><?= Yii::$app->user->identity->username ?></p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Smart Home', 'options' => ['class' => 'header']],
                    ['label' => 'Панель Управления', 'icon' => 'fa fa-sliders', 'url' => ['/panel/index']],
                    ['label' => 'История', 'icon' => 'fa fa-th-list', 'url' => ['/history/index']],

                    ['label' => 'Администрирование', 'options' => ['class' => 'header']],
                    ['label' => 'Элементы', 'icon' => 'fa fa-toggle-on', 'url' => ['/admin/item/index']],
                    ['label' => 'Устройства', 'icon' => 'fa fa-hdd-o', 'url' => ['/admin/board/index']],
                    ['label' => 'Триггеры', 'icon' => 'fa fa-feed', 'url' => ['/admin/trigger/index']],
                    ['label' => 'Задачи', 'icon' => 'fa fa-check', 'url' => ['/admin/task/index']],
                    ['label' => 'Комнаты', 'icon' => 'fa fa-folder-open', 'url' => ['/admin/room/index']],
                    ['label' => 'Параметры', 'icon' => 'fa fa-cogs', 'url' => ['/admin/setting/index']],
//                    ['label' => 'История', 'icon' => 'fa fa-bar-chart', 'url' => ['/admin/history/index']],
                    ['label' => 'Пользователи', 'icon' => 'fa fa-users', 'url' => ['/admin/user/index']],
                ],
            ]
        ) ?>

    </section>

</aside>
