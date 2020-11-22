<?php

namespace SMA\PAA\AINO;

use PHPUnit\Framework\TestCase;
use SMA\PAA\FAKECURL\FakeApi;

final class AinoClientTest extends TestCase
{
    public function testAinoMessageIsGeneratedCorreclyWhenIdsAndMeta(): void
    {
        require_once __DIR__ . "/../FAKECURL/FakeApi.php";
        $api = new FakeApi();
        $aino = new AinoClient("fake-key-for-aino", "Foo", "Bar", $api);
        $timestamp = "2019-11-27T22:41:04Z";
        $aino->succeeded(
            $timestamp,
            "some message",
            "some operation",
            "some payload type",
            ["fooId" => 1],
            ["test" => "meta"]
        );
        $this->assertEquals(
            '{"transactions":[{"from":"Foo","to":"Bar","status":"success",'
            . '"message":"some message",'
            . '"timestamp":1574894464000,"operation":"some operation","payloadType":"some payload type",'
            . '"ids":[{"idType":"fooId","values":["1"]}],'
            . '"metadata":[{"name":"test","value":"meta"}]}]}',
            json_encode($api->sentValues())
        );
    }
    public function testAinoMessageIsGeneratedCorreclyWhenIdsWithMultipleKeys(): void
    {
        require_once __DIR__ . "/../FAKECURL/FakeApi.php";
        $api = new FakeApi();
        $aino = new AinoClient("fake-key-for-aino", "Foo", "Bar", $api);
        $timestamp = "2019-11-27T22:41:04Z";
        $aino->succeeded(
            $timestamp,
            "some message",
            "some operation",
            "some payload type",
            ["fooId" => array(1, 2, 3)],
            ["test" => "meta"]
        );
        $this->assertEquals(
            '{"transactions":[{"from":"Foo","to":"Bar","status":"success",'
            . '"message":"some message",'
            . '"timestamp":1574894464000,"operation":"some operation","payloadType":"some payload type",'
            . '"ids":[{"idType":"fooId","values":["1","2","3"]}],'
            . '"metadata":[{"name":"test","value":"meta"}]}]}',
            json_encode($api->sentValues())
        );
    }
    public function testAinoMessageIsGeneratedCorreclyWhenFlowId(): void
    {
        require_once __DIR__ . "/../FAKECURL/FakeApi.php";
        $api = new FakeApi();
        $aino = new AinoClient("fake-key-for-aino", "Foo", "Bar", $api);
        $timestamp = "2019-11-27T22:41:04Z";
        $aino->succeeded(
            $timestamp,
            "some message",
            "some operation",
            "some payload type",
            ["fooId" => 1],
            ["test" => "meta"],
            "dummyflowid"
        );
        $this->assertEquals(
            '{"transactions":[{"from":"Foo","to":"Bar","status":"success",'
            . '"message":"some message",'
            . '"timestamp":1574894464000,"operation":"some operation","payloadType":"some payload type",'
            . '"ids":[{"idType":"fooId","values":["1"]}],'
            . '"metadata":[{"name":"test","value":"meta"}],"flowId":"dummyflowid"}]}',
            json_encode($api->sentValues())
        );
    }
}
