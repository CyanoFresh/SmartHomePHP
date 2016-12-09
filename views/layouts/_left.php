<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= Yii::$app->user->identity->getAvatar() ?>" class="img-circle" alt="User Image">
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

                    ['label' => 'Информация', 'options' => ['class' => 'header']],
                    ['label' => 'История', 'icon' => 'fa fa-bar-chart', 'url' => ['/history/index']],

                    ['label' => 'Настройки', 'options' => ['class' => 'header']],
                    ['label' => 'Элементы', 'icon' => 'fa fa-cubes', 'url' => ['/admin/item/index']],
                    ['label' => 'Платы', 'icon' => 'fa fa-cogs', 'url' => ['/admin/board/index']],
                    ['label' => 'Triggers', 'icon' => 'fa fa-feed', 'url' => ['/admin/trigger/index']],
                    ['label' => 'Задачи', 'icon' => 'fa fa-check', 'url' => ['/admin/task/index']],
                    ['label' => 'Комнаты', 'icon' => 'fa fa-folder-open', 'url' => ['/admin/room/index']],
//                    ['label' => 'История', 'icon' => 'fa fa-bar-chart', 'url' => ['/admin/history/index']],
                    ['label' => 'Пользователи', 'icon' => 'fa fa-users', 'url' => ['/admin/user/index']],

                    ['label' => 'Yii2', 'options' => ['class' => 'header'], 'visible' => YII_DEBUG],
                    ['label' => 'Gii', 'icon' => 'fa fa-file-code-o', 'url' => ['/gii'], 'visible' => YII_DEBUG],
                    ['label' => 'Debug', 'icon' => 'fa fa-dashboard', 'url' => ['/debug'], 'visible' => YII_DEBUG],
                ],
            ]
        ) ?>

    </section>

</aside>
