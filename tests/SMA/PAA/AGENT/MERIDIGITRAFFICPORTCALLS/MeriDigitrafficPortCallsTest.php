<?php

namespace SMA\PAA\AGENT\MERIDIGITRAFFICPORTCALLS;

use PHPUnit\Framework\TestCase;

use SMA\PAA\FAKECURL\FakeCurlRequest;
use SMA\PAA\FAKERESULTPOSTER\FakeResultPoster;
use SMA\PAA\AGENT\ApiConfig;

final class MeriDigitrafficPortCallsTest extends TestCase
{
    public function testConstructor(): void
    {
        # Comply with PSR-1 2.3 Side Effects Rule
        require_once(__DIR__."/../../FAKECURL/FakeCurlRequest.php");
        require_once(__DIR__."/../../FAKERESULTPOSTER/FakeResultPoster.php");
        $meriDigitrafficPortCalls = new MeriDigitrafficPortCalls(
            new FakeCurlRequest(),
            new FakeResultPoster()
        );
        $this->assertEquals(isset($meriDigitrafficPortCalls), true);
    }

    public function testExecute(): void
    {
        $curlRequest = new FakeCurlRequest();
        $resultPoster = new FakeResultPoster();
        $meriDigitrafficPortCalls = new MeriDigitrafficPortCalls($curlRequest, $resultPoster);
        $curlRequest->executeReturn = file_get_contents(__DIR__."/ValidServerData.json");
        $meriDigitrafficPortCalls->execute(new ApiConfig("key", "http//url/foo", ["foo"]));
        // file_put_contents(__DIR__."/ValidPosterData.json", json_encode($resultPoster->results, JSON_PRETTY_PRINT));
        $this->assertEquals(
            $resultPoster->results,
            json_decode(file_get_contents(__DIR__."/ValidPosterData.json"), true)
        );
    }

    public function testFetchResultsAllGood(): void
    {
        $curlRequest = new FakeCurlRequest();
        $meriDigitrafficPortCalls = new MeriDigitrafficPortCalls($curlRequest, new FakeResultPoster());
        $curlRequest->executeReturn = file_get_contents(__DIR__."/ValidServerData.json");
        $this->assertEquals($meriDigitrafficPortCalls->fetchResults(), json_decode($curlRequest->executeReturn, true));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Error occured during curl exec
     */
    public function testFetchResultsCurlResponseFalse(): void
    {
        $curlRequest = new FakeCurlRequest();
        $meriDigitrafficPortCalls = new MeriDigitrafficPortCalls($curlRequest, new FakeResultPoster());
        $curlRequest->executeReturn = false;
        $meriDigitrafficPortCalls->fetchResults();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Error response from server
     */
    public function testFetchResultsErrorSet(): void
    {
        $curlRequest = new FakeCurlRequest();
        $meriDigitrafficPortCalls = new MeriDigitrafficPortCalls($curlRequest, new FakeResultPoster());
        $curlRequest->executeReturn = json_encode(["error" => "Dummy error"]);
        $meriDigitrafficPortCalls->fetchResults();
    }

    public function testParseResultsValidData(): void
    {
        $meriDigitrafficPortCalls = new MeriDigitrafficPortCalls(new FakeCurlRequest(), new FakeResultPoster());
        $fetchedData = json_decode(file_get_contents(__DIR__."/ValidServerData.json"), true);
        $parsedData = $meriDigitrafficPortCalls->parseResults($fetchedData);
        // file_put_contents(__DIR__."/ValidParsedData.json", json_encode($parsedData, JSON_PRETTY_PRINT));
        $this->assertEquals($parsedData, json_decode(file_get_contents(__DIR__."/ValidParsedData.json"), true));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing portCalls array in input array.
     */
    public function testParseResultsMissingPortCalls(): void
    {
        $meriDigitrafficPortCalls = new MeriDigitrafficPortCalls(new FakeCurlRequest(), new FakeResultPoster());
        $fetchedData = json_decode(file_get_contents(__DIR__."/MissingPortCallsServerData.json"), true);
        $meriDigitrafficPortCalls->parseResults($fetchedData);
    }

    public function testPostResults(): void
    {
        $resultPoster = new FakeResultPoster();
        $meriDigitrafficPortCalls = new MeriDigitrafficPortCalls(new FakeCurlRequest(), $resultPoster);
        $parsedData = json_decode(file_get_contents(__DIR__."/ValidParsedData.json"), true);
        $meriDigitrafficPortCalls->postResults(new ApiConfig("key", "http//url/foo", ["foo"]), $parsedData);
        $this->assertEquals(
            $resultPoster->results,
            json_decode(file_get_contents(__DIR__."/ValidPosterData.json"), true)
        );
    }
}
