<?php

namespace app\components;

use app\models\Item;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Component;
use yii\helpers\Json;

class ApiHelper extends Component implements ApiHelperInterface
{
    /**
     * @var Item
     */
    public $item;

    /**
     * ApiHelper constructor.
     * @param Item $item
     */
    public function __construct(Item $item)
    {
        parent::__construct();

        $this->item = $item;

        return $this;
    }

    /**
     * Get CURL instance
     *
     * @param int $timeout
     * @return Curl
     */
    public function getCurl($timeout = 2)
    {
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT, $timeout);

        return $curl;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->item->board->baseUrl;
    }

    /**
     * Make request to the API provider
     *
     * @param $url
     * @return array
     */
    public function makeRequestToApi($url)
    {
        $response = $this->getCurl()->get($this->getBaseUrl() . $url);

        return Json::decode($response);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $data = $this->makeRequestToApi($this->item->url);

        return $data['value'];
    }

    /**
     * Turn item on and return current state
     *
     * @return array
     */
    public function turnOn()
    {
        return $this->makeRequestToApi($this->item->url . '/1');
    }

    /**
     * Turn item off and return current state
     *
     * @return array
     */
    public function turnOff()
    {
        return $this->makeRequestToApi($this->item->url . '/0');
    }
}
