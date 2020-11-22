<?php
namespace SMA\PAA\FAKECURL;

use SMA\PAA\CURL\IApi;

class FakeApi implements IApi
{
    private $sentValues = [];
    public function post(string $sessionId, string $path, array $values)
    {
        $this->sentValues = $values;
    }
    public function postWithAuthorizationKey(string $authorizationKey, string $path, array $values)
    {
        $this->sentValues = $values;
    }
    public function put(string $sessionId, string $path, array $values)
    {
        $this->sentValues = $values;
    }
    public function get(string $sessionId, string $path)
    {
    }
    public function sentValues()
    {
        return $this->sentValues;
    }
}
