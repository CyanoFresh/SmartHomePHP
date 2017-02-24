<?php

/* @var $this yii\web\View */
/* @var $models app\models\Setting[] */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Настройки';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="setting-index">

    <form action="<?= Url::to(['save']) ?>" method="post">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>">

        <?php foreach ($models as $model): ?>
            <div class="form-group">
                <label for="<?= $model->key ?>"><?= $model->title ?></label>
                <input type="text"
                       class="form-control"
                       id="<?= $model->key ?>"
                       name="Settings[<?= $model->key ?>]"
                       value="<?= $model->value ?>">
            </div>
        <?php endforeach; ?>

        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        </div>
    </form>

</div>
