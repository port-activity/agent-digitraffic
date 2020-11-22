<?php
namespace SMA\PAA\CURL;

interface IApi
{
    public function post(string $sessionId, string $path, array $values);
    public function postWithAuthorizationKey(string $authorizationKey, string $path, array $values);
    public function put(string $sessionId, string $path, array $values);
    public function get(string $sessionId, string $path);
}
