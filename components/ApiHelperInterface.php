<?php

namespace app\components;

interface ApiHelperInterface
{
    public function getCurl($timeout = 2);
    public function getBaseUrl();
    public function makeRequestToApi($url);
    public function getValue();
}
