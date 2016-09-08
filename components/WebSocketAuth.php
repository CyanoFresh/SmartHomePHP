<?php

namespace app\components;

use app\models\User;
use Yii;

class WebSocketAuth
{
    /**
     * @return null|string
     */
    public static function getAuthKey()
    {
        $user = User::findOne(Yii::$app->user->id);

        if (!$user) {
            return null;
        }

        return $user->auth_key;
    }
}
