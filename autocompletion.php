<?php

/**
 * Yii bootstrap file.
 * Used for enhanced IDE code autocompletion.
 * Note: To avoid "Multiple Implementations" PHPStorm warning and make autocomplete faster
 * exclude or "Mark as Plain Text" vendor/yiisoft/yii2/Yii.php file
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication the application instance
     */
    public static $app;
}

/**
 * Class BaseApplication
 * Used for properties that are identical for both WebApplication and ConsoleApplication
 */
abstract class BaseApplication extends yii\base\Application
{
}

/**
 * Class WebApplication
 * Include only Web application related components here
 *
 * @property User $user
 */
class WebApplication extends yii\web\Application
{
}

/**
 * Class ConsoleApplication
 * Include only Console application related components here
 */
class ConsoleApplication extends yii\console\Application
{
}

/**
 * User component
 * Include only Web application related components here
 *
 * @property \app\models\User $identity User model.
 */
class User extends \yii\web\User
{
}

/**
 * Class Connection
 *
 * @property integer $resourceId
 * @property \app\models\User $User
 * @property boolean $api
 * @property string $remoteAddress
 */
class Connection extends \Ratchet\WebSocket\Version\RFC6455\Connection
{

}

/**
 * Class BoardConnection
 *
 * @property \app\models\Board $Board
 * @property integer $lastPingAt
 */
class BoardConnection extends Connection
{
}
