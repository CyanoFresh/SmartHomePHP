<?php

namespace app\modules\server\events;

use app\models\Device;

class DeviceAuthEvent extends ConnectionEvent
{
    /**
     * @var Device
     */
    public $device;
}
