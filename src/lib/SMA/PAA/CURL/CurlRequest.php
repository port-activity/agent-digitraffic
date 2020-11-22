<?php
namespace SMA\PAA\CURL;

class CurlRequest implements ICurlRequest
{
    private $handle = null;

    public function init($url)
    {
        $this->handle = curl_init($url);
    }

    public function setOption($name, $value)
    {
        curl_setopt($this->handle, $name, $value);
    }

    public function execute()
    {
        return curl_exec($this->handle);
    }

    public function getInfo($name = null)
    {
        if (isset($name)) {
            return curl_getinfo($this->handle, $name);
        } else {
            return curl_getinfo($this->handle);
        }
    }

    public function close()
    {
        curl_close($this->handle);
    }
}
