<?php

/* @var $this yii\web\View */

$this->title = 'Главная';

\yii\helpers\VarDumper::dump(\app\models\Setting::getValueByKey('log.user_connection'),10,true);
?>
<div class="site-index">
    <h1 class="page-header"><?= $this->title ?></h1>
</div>
