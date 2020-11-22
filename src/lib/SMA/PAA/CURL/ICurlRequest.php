<?php
namespace SMA\PAA\CURL;

interface ICurlRequest
{
    public function init($url);
    public function setOption($name, $value);
    public function execute();
    public function getInfo($name = null);
    public function close();
}
