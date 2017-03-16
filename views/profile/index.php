<?php

/** @var $this \yii\web\View */
/** @var $user \app\models\User */

use yii\helpers\Url;

$this->title = 'Профиль ' . $user->username;
?>

<div class="row user-profile">
    <div class="col-sm-2">
        <img src="<?= $user->getAvatar(200) ?>" alt="<?= $user->username ?>" class="img-responsive img-circle center-block">

        <div class="user-actions text-center">
            <a href="<?= Url::to(['edit']) ?>" class="btn btn-primary"><i class="fa fa-pencil"></i>
                Изменить</a>
        </div>
    </div>
    <div class="col-sm-5">
        <h4><i class="fa fa-user"></i> Информация</h4>

        <table class="table">
            <tbody>
            <tr>
                <th scope="row">ID</th>
                <td><?= $user->id ?></td>
            </tr>
            <tr>
                <th scope="row">Логин</th>
                <td><?= $user->username ?></td>
            </tr>
            <tr>
                <th scope="row">Email</th>
                <td><?= $user->email ?></td>
            </tr>
            <tr>
                <th scope="row">Группа</th>
                <td><?= $user->getGroupLabel() ?></td>
            </tr>
            <tr>
                <th scope="row">Имя</th>
                <td><?= $user->name ?></td>
            </tr>
            <tr>
                <th scope="row">Комната</th>
                <td><?= $user->room->name ?></td>
            </tr>
            <?php if (Yii::$app->user->id === $user->id): ?>
            <tr>
                <th scope="row">API Ключ</th>
                <td><span class="show-on-click" data-text="<?= $user->api_key ?>">показать</span></td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
