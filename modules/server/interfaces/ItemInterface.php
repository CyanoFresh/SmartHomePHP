<?php

namespace app\modules\server\interfaces;

use app\models\query\DeviceQuery;

/**
 * Interface ItemInterface
 * @package app\modules\server\interfaces
 */
interface ItemInterface
{
    /**
     * @return DeviceQuery
     */
    public function getDevice();
}
