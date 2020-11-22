<?php
namespace SMA\PAA\AGENT;

require_once "init.php";

use SMA\PAA\CURL\CurlRequest;
use SMA\PAA\RESULTPOSTER\ResultPoster;
use SMA\PAA\AGENT\MERIDIGITRAFFICPORTCALLS\MeriDigitrafficPortCalls;
use SMA\PAA\AINO\AinoClient;
use Exception;

$apiKey = getenv("API_KEY");
$apiUrl = getenv("API_URL");
$ainoKey = getenv("AINO_API_KEY");
$apiParameters = ["imo", "vessel_name", "time_type", "state", "time", "payload"];

$apiConfig = new ApiConfig($apiKey, $apiUrl, $apiParameters);

$aino = null;
if ($ainoKey) {
    $toApplication = parse_url($apiUrl, PHP_URL_HOST);
    $aino = new AinoClient($ainoKey, "Digitraffic", $toApplication);
}
$agent = new MeriDigitrafficPortCalls(new CurlRequest(), new ResultPoster(new CurlRequest()), $aino);

$aino = null;
if ($ainoKey) {
    $aino = new AinoClient($ainoKey, "Digitraffic service", "Digitraffic");
}
$ainoTimestamp = gmdate("Y-m-d\TH:i:s\Z");

try {
    $counts = $agent->execute($apiConfig);
    if (isset($aino)) {
        $aino->succeeded($ainoTimestamp, "Digitraffic agent succeeded", "Batch run", "timestamp", [], $counts);
    }
} catch (\Exception $e) {
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
    if (isset($aino)) {
        $aino->failure($ainoTimestamp, "Digitraffic agent failed", "Batch run", "timestamp", [], []);
    }
}
