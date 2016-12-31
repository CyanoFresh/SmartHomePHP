<?php

namespace app\helpers;

class IPHelper
{
    /**
     * @param string $ip
     * @return bool
     */
    public static function isLocal($ip)
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
    }
}
