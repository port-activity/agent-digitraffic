<?php
namespace SMA\PAA\RESULTPOSTER;

use SMA\PAA\AGENT\ApiConfig;

interface IResultPoster
{
    public function resultChecksum(ApiConfig $apiConfig, array $result): ?string;
    public function postResult(ApiConfig $apiConfig, array $result);
}
