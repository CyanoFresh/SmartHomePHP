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
                    ['label' => 'Управление', 'options' => ['class' => 'header']],
                    ['label' => 'Панель Управления', 'icon' => 'fa fa-sliders', 'url' => ['/panel/index']],
                    ['label' => 'История', 'icon' => 'fa fa-th-list', 'url' => ['/history/index']],

                    ['label' => 'Администрирование', 'options' => ['class' => 'header'], 'visible' => Yii::$app->user->identity->isAdmin],
                    ['label' => 'Элементы', 'icon' => 'fa fa-toggle-on', 'url' => ['/admin/item/index'], 'visible' => Yii::$app->user->identity->isAdmin],
                    ['label' => 'Устройства', 'icon' => 'fa fa-hdd-o', 'url' => ['/admin/board/index'], 'visible' => Yii::$app->user->identity->isAdmin],
                    ['label' => 'События', 'icon' => 'fa fa-code-fork', 'url' => ['/admin/event/index'], 'visible' => Yii::$app->user->identity->isAdmin],
                    ['label' => 'Триггеры', 'icon' => 'fa fa-feed', 'url' => ['/admin/trigger/index'], 'visible' => Yii::$app->user->identity->isAdmin],
                    ['label' => 'Задачи', 'icon' => 'fa fa-check', 'url' => ['/admin/task/index'], 'visible' => Yii::$app->user->identity->isAdmin],
                    ['label' => 'Комнаты', 'icon' => 'fa fa-folder-open', 'url' => ['/admin/room/index'], 'visible' => Yii::$app->user->identity->isAdmin],
                    ['label' => 'Параметры', 'icon' => 'fa fa-cogs', 'url' => ['/admin/setting/index'], 'visible' => Yii::$app->user->identity->isAdmin],
//                    ['label' => 'История', 'icon' => 'fa fa-bar-chart', 'url' => ['/admin/history/index'], 'visible' => Yii::$app->user->identity->isAdmin],
                    ['label' => 'Пользователи', 'icon' => 'fa fa-users', 'url' => ['/admin/user/index'], 'visible' => Yii::$app->user->identity->isAdmin],
                ],
            ]
        ) ?>

    </section>

</aside>
