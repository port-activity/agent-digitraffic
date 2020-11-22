<?php
namespace SMA\PAA\CURL;

use Exception;

final class Api implements IApi
{
    private $rootUrl;
    public function __construct(string $rootUrl)
    {
        if (!preg_match("|^http[s]{0,1}:/|", $rootUrl)) {
            throw new Exception("Invalid protocol: " . $rootUrl);
        }
        if (!preg_match(",/$,", $rootUrl)) {
            throw new Exception("Api root url should end with '/'");
        }
        $this->rootUrl = $rootUrl;
    }
    private function call(string $method, string $sessionId, string $authorizationKey, string $path, array $values)
    {
        $url = $this->rootUrl . $path;
        $data = json_encode($values);

        $headers = [
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        if ($sessionId) {
            $headers[] = "Authorization: Bearer $sessionId";
        }
        if ($authorizationKey) {
            $headers[] = "Authorization: $authorizationKey";
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $method === "post" && curl_setopt($ch, CURLOPT_POST, 1);
        $method === "put" && curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $method === "get" && curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // note: eg. aino.io return 202 http status code
        if ($http_code >= 300) {
            error_log($response);
            return false;
        }

        // $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        return json_decode($body, true);
    }
    public function post(string $sessionId, string $path, array $values)
    {
        return $this->call("post", $sessionId, "", $path, $values);
    }
    public function postWithAuthorizationKey(string $authorizationKey, string $path, array $values)
    {
        return $this->call("post", "", $authorizationKey, $path, $values);
    }
    public function put(string $sessionId, string $path, array $values)
    {
        return $this->call("put", $sessionId, "", $path, $values);
    }
    public function get(string $sessionId, string $path)
    {
        return $this->call("get", $sessionId, "", $path, []);
    }
}
