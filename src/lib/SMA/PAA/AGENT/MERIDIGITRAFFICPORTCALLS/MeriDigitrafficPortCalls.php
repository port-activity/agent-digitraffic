<?php
namespace SMA\PAA\AGENT\MERIDIGITRAFFICPORTCALLS;

use SMA\PAA\CURL\ICurlRequest;
use SMA\PAA\RESULTPOSTER\IResultPoster;
use SMA\PAA\AGENT\ApiConfig;
use SMA\PAA\AINO\AinoClient;

use Exception;
use InvalidArgumentException;
use DateTimeInterface;
use DateTime;
use DateInterval;

class MeriDigitrafficPortCalls
{
    private $config;
    private $serviceUrl;
    private $curlRequest;
    private $resultPoster;
    private $aino;

    public function __construct(ICurlRequest $curlRequest, IResultPoster $resultPoster, AinoClient $aino = null)
    {
        $this->curlRequest = $curlRequest;
        $this->resultPoster = $resultPoster;
        $this->aino = $aino;

        $this->config = require("MeriDigitrafficPortCallsConfig.php");
        date_default_timezone_set("UTC");
        $dateInterval = new DateInterval("PT".$this->config["offsetminutes"]."M");
        $dateFrom = new DateTime();
        $dateFrom->sub($dateInterval);
        $from = str_replace("+00:00", ".000Z", $dateFrom->format(DateTimeInterface::ATOM));
        $this->serviceUrl = $this->config["serviceurl"].$this->config["locationcode"]."?from=".$from;
    }

    public function execute(ApiConfig $apiConfig)
    {
        $rawResults = $this->fetchResults();
        $parsedResults = $this->parseResults($rawResults);
        return $this->postResults($apiConfig, $parsedResults);
    }

    public function fetchResults(): array
    {
        $this->curlRequest->init($this->serviceUrl);
        $this->curlRequest->setOption(CURLOPT_ENCODING, ""); // allow all encodings, gzip etc.
        $this->curlRequest->setOption(CURLOPT_RETURNTRANSFER, true);
        $curlResponse = $this->curlRequest->execute();

        if ($curlResponse === false) {
            $info = $this->curlRequest->getInfo();
            $this->curlRequest->close();
            throw new Exception("Error occured during curl exec.\ncurl_getinfo returns:\n".print_r($info, true)."\n");
        }

        $this->curlRequest->close();
        $decoded = json_decode($curlResponse, true);

        if (isset($decoded["error"])) {
            throw new Exception("Error response from server ".$this->serviceUrl.":\n".print_r($decoded, true)."\n");
        }

        return $decoded;
    }

    public function parseResults(array $rawResults): array
    {
        $parsedResults = array();

        if (!isset($rawResults["portCalls"])) {
            throw new InvalidArgumentException("Missing portCalls array in input array.");
        }

        $portCalls = $rawResults["portCalls"];

        foreach ($portCalls as $portCall) {
            $result = array();

            $result["imo"] = 0;
            foreach ($this->config["parametermappings"] as $in => $out) {
                if (isset($portCall[$in])) {
                    $result[$out] = $portCall[$in];
                }
            }

            $payload = [];
            foreach ($this->config["payloadmappings"] as $in => $out) {
                if (isset($portCall[$in])) {
                    $payload[$out] = $portCall[$in];
                }
            }

            # Clean 0 mmsi from payload
            if (isset($payload["mmsi"])) {
                if ($payload["mmsi"] === 0) {
                    unset($payload["mmsi"]);
                }
            }

            $payload["source"] = "digitraffic";

            #todo do we need some magic to determine ts type from port area details
            foreach ($portCall["portAreaDetails"] as $portAreaDetail) {
                if (isset($portAreaDetail["berthCode"])
                && $portAreaDetail["berthCode"] !== "MUU") {
                    $payload["berth_name"] = $portAreaDetail["berthCode"];
                }

                if (isset($portAreaDetail["berthName"])
                && $portAreaDetail["berthName"] !== "Ei tiedossa") {
                    $payload["berth_name"] = $portAreaDetail["berthName"];
                }

                foreach ($this->config["timestampmappings"] as $in => $out) {
                    if (isset($portAreaDetail[$in])) {
                        $result["time_type"] = $out["time_type"];
                        $result["state"] = $out["state"];
                        $datetime = DateTime::createFromFormat("Y-m-d\TH:i:s.uO", $portAreaDetail[$in]);
                        $result["time"] = $datetime->format("Y-m-d\TH:i:sO");

                        $result["payload"] = $payload;

                        $parsedResults[] = $result;
                    }
                }
            }
        }

        return $parsedResults;
    }

    public function postResults(ApiConfig $apiConfig, array $results)
    {
        $countOk = 0;
        $countFailed = 0;

        $ainoTimestamp = gmdate("Y-m-d\TH:i:s\Z");

        foreach ($results as $result) {
            $ainoFlowId = $this->resultPoster->resultChecksum($apiConfig, $result);
            try {
                $this->resultPoster->postResult($apiConfig, $result);
                ++$countOk;
                if (isset($this->aino)) {
                    $this->aino->succeeded(
                        $ainoTimestamp,
                        "Digitraffic agent succeeded",
                        "Post",
                        "timestamp",
                        ["imo" => $result["imo"]],
                        [],
                        $ainoFlowId
                    );
                }
            } catch (\Exception $e) {
                ++$countFailed;
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
                if (isset($this->aino)) {
                    $this->aino->failure(
                        $ainoTimestamp,
                        "Digitraffic agent failed",
                        "Post",
                        "timestamp",
                        [],
                        [],
                        $ainoFlowId
                    );
                }
            }
        }

        return [
            "ok" => $countOk,
            "failed" => $countFailed
        ];
    }
}
