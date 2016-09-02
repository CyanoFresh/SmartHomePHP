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

        $auth_key = $user->auth_key;

        // Regenerate auth key
        $user->generateAuthKey();
        $user->save();

        return $auth_key;
    }
}
