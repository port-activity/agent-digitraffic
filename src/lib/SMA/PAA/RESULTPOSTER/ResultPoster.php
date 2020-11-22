<?php
namespace SMA\PAA\RESULTPOSTER;

use Exception;
use InvalidArgumentException;
use SMA\PAA\AGENT\ApiConfig;
use SMA\PAA\CURL\ICurlRequest;

class ResultPoster implements IResultPoster
{
    private $curlRequest;

    public function __construct(ICurlRequest $curlRequest)
    {
        $this->curlRequest = $curlRequest;
    }

    public function resultChecksum(ApiConfig $apiConfig, array $result): ?string
    {
        ksort($result);

        foreach ($apiConfig->parameters() as $apiParameter) {
            if (!isset($result[$apiParameter])) {
                return null;
            }
        }

        foreach ($result as $resultKey => $resultValue) {
            if (!in_array($resultKey, $apiConfig->parameters())) {
                return null;
            }
        }

        $jsonResult = json_encode($result);

        if ($jsonResult === false) {
            return null;
        }

        return md5($jsonResult);
    }

    public function postResult(ApiConfig $apiConfig, array $result)
    {

        foreach ($apiConfig->parameters() as $apiParameter) {
            if (!isset($result[$apiParameter])) {
                throw new InvalidArgumentException("Parameter ".$apiParameter." missing from input array.");
            }
        }

        foreach ($result as $resultKey => $resultValue) {
            if (!in_array($resultKey, $apiConfig->parameters())) {
                throw new InvalidArgumentException("Invalid parameter ".$resultKey." in input array.");
            }
        }
        $apiUrl = $apiConfig->url();
        $postPayload = json_encode($result);

        $this->curlRequest->init($apiUrl);
        $this->curlRequest->setOption(CURLOPT_POSTFIELDS, $postPayload);
        $header = array();
        $header[] = "Content-type: application/json";
        $header[] = "Authorization: ApiKey " . $apiConfig->key();
        $this->curlRequest->setOption(CURLOPT_HTTPHEADER, $header);
        $this->curlRequest->setOption(CURLOPT_RETURNTRANSFER, true);
        $curlResponse = $this->curlRequest->execute();
        $info = $this->curlRequest->getInfo();
        if ($info["http_code"] !== 200) {
            $this->curlRequest->close();
            $decoded = json_decode($curlResponse, true);

            if (isset($decoded["error"])) {
                throw new Exception("Error response from server ".$apiUrl.":\n".print_r($decoded, true)."\n");
            }
            if (isset($decoded["result"])) {
                if ($decoded["result"] === "ERROR") {
                    throw new Exception("Error result from server ".$apiUrl.":\n".print_r($decoded, true)."\n");
                }
            }
            throw new Exception("Error occured during curl exec.\ncurl_getinfo returns:\n".print_r($info, true)."\n");
        }
    }
}
