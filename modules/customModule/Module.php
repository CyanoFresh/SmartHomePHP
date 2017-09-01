<?php

namespace app\modules\customModule;

use app\modules\BaseModule;
use app\modules\server\components\CoreServer;

/**
 * customModule module definition class
 */
class Module extends BaseModule
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\customModule\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function getEventHandlers()
    {
        return [
            [
                CoreServer::EVENT_INIT,
                /** @see EventHandler::onServerInit() */
                [EventHandler::class, 'onServerInit']
            ],
            [
                CoreServer::EVENT_CONNECTION_MESSAGE,
                /** @see EventHandler::onConnectionOpen() */
                [EventHandler::class, 'onMessage']
            ],
        ];
    }
}
