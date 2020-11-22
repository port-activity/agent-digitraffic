<?php
namespace SMA\PAA\FAKERESULTPOSTER;

use SMA\PAA\RESULTPOSTER\IResultPoster;
use SMA\PAA\AGENT\ApiConfig;

class FakeResultPoster implements IResultPoster
{
    public $results;

    public function resultChecksum(ApiConfig $apiConfig, array $result): ?string
    {
        return "MD5string";
    }

    public function postResult(ApiConfig $apiConfig, array $result)
    {
        $this->results[] = $result;
    }
}
