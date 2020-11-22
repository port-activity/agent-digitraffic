<?php

namespace SMA\PAA\CURL;

use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase
{
    public function testApiAcceptsHttpsProtocol(): void
    {
        $api = new Api("https://moro/");
        $this->assertEquals('SMA\PAA\CURL\Api', get_class($api));
    }
    public function testApiAcceptsHttpProtocol(): void
    {
        $api = new Api("http://moro/");
        $this->assertEquals('SMA\PAA\CURL\Api', get_class($api));
    }
    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid protocol: //moro/
     */
    public function testApicceptsHttpProtocol(): void
    {
        new Api("//moro/");
    }
}
