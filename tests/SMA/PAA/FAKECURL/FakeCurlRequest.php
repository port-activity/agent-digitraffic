<?php
namespace SMA\PAA\FAKECURL;

use SMA\PAA\CURL\ICurlRequest;

class FakeCurlRequest implements ICurlRequest
{
    public $url;
    public $executeReturn;
    public $getInfoReturn;
    public $optArray;

    public function init($url)
    {
        $this->url = $url;
    }

    public function setOption($name, $value)
    {
        $this->optArray[$name] = $value;
    }

    public function execute()
    {
        return $this->executeReturn;
    }

    public function getInfo($name = null)
    {
        return $this->getInfoReturn;
    }

    public function close()
    {
    }
}
