<?php

namespace app\modules\server\events;

use app\models\User;

class UserAuthEvent extends ConnectionEvent
{
    /**
     * @var User
     */
    public $user;
}
