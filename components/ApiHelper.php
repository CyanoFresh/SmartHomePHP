<?php

namespace app\components;

use app\models\Item;
use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;

class ApiHelper
{
    /**
     * Get CURL instance
     *
     * @param int $timeout
     * @return Curl
     */
    public static function getCurl($timeout = 2)
    {
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT, $timeout);

        return $curl;
    }

    /**
     * @return string
     */
    public static function getBaseUrl($item)
    {
        return Yii::$app->params['apiBaseUrl'];
    }

    /**
     * Make request to the API provider
     *
     * @param $url
     * @return array
     */
    public static function makeRequest($url)
    {
        $curl = self::getCurl();
        $response = $curl->get(self::getBaseUrl() . $url);

        return Json::decode($response);
    }

    /**
     * @param Item $item
     * @return mixed
     */
    public static function getItemValue($item)
    {
        $data = self::makeRequest($item->url);

        return $data['value'];
    }

    /**
     * Turn item on and return current state
     *
     * @param Item $item
     * @return boolean
     */
    public static function itemTurnOn($item)
    {
        return self::makeRequest($item->url . '/1');
    }

    /**
     * Turn item off and return current state
     *
     * @param Item $item
     * @return boolean
     */
    public static function itemTurnOff($item)
    {
        return self::makeRequest($item->url . '/0');
    }
}
