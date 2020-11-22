<?php
namespace SMA\PAA\AINO;

use SMA\PAA\CURL\IApi;
use SMA\PAA\CURL\Api;
use Exception;

class AinoClient
{
    private $apiKey;
    private $from;
    private $to;
    public function __construct(string $apiKey, string $from, string $to, IApi $api = null)
    {
        $this->apiKey = $apiKey;
        $this->from = $from;
        $this->to = $to;
        $this->api = $api ? $api : new Api("https://data.aino.io/rest/v2.0/");
    }
    public function succeeded(
        string $timestamp,
        string $message,
        string $operation,
        string $payloadType,
        array $ids,
        array $meta,
        string $flowId = null
    ) {
        return $this->sendTransaction($timestamp, "success", $message, $operation, $payloadType, $ids, $meta, $flowId);
    }
    public function failure(
        string $timestamp,
        string $message,
        string $operation,
        string $payloadType,
        array $ids,
        array $meta,
        string $flowId = null
    ) {
        return $this->sendTransaction($timestamp, "failure", $message, $operation, $payloadType, $ids, $meta, $flowId);
    }
    private function sendTransaction(
        string $timestamp,
        string $status,
        string $message,
        string $operation,
        string $payloadType,
        array $ids,
        array $meta,
        string $flowId = null
    ) {
        $api = $this->api;
        if (!in_array($status, ["success", "failure"])) {
            throw new Exception("Invalid status");
        }
        $transaction = [
            "from"  => $this->from,
            "to"    => $this->to,
            "status" => $status,
            "message" => $message,
            "timestamp" => strtotime($timestamp) * 1000, // milliseconds
            "operation" => $operation,
            "payloadType" => $payloadType,
            "ids" => array_map(function ($k, $v) {
                $values = is_array($v) ? $v : [$v];
                return [
                    "idType" => $k,
                    "values" => array_map(function ($vv) {
                        return "" . $vv;
                    }, $values),
                ];
            }, array_keys($ids), $ids),
            "metadata" => array_map(function ($k, $v) {
                return [
                    "name" => $k,
                    "value" => $v
                ];
            }, array_keys($meta), $meta)
        ];
        if (isset($flowId)) {
            $transaction["flowId"] = $flowId;
        }

        $transactions["transactions"][] = $transaction;

        $api->postWithAuthorizationKey("apikey " . $this->apiKey, "transaction", $transactions);
    }
}
