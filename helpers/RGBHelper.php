<?php

namespace app\helpers;

class RGBHelper
{
    /**
     * @param integer $value
     * @return integer
     */
    public static function from8to10($value)
    {
        return $value * 4;
    }

    /**
     * @param integer $value
     * @return float|integer
     */
    public static function from10to8($value)
    {
        return floor($value / 4);
    }
}
