<?php

use NavOnlineInvoice\BaseRequestXml;

// Note
class MyBaseRequestXml extends BaseRequestXml
{
    protected string $rootName = "Test";
}


class BaseRequestXmlTest extends BaseTest
{
    private function createTestRequestXml(): MyBaseRequestXml
    {
        return new MyBaseRequestXml($this->getConfig());
    }


    public function testResponseXml(): void
    {
        $requestXml = $this->createTestRequestXml();
        $xmlObj = $requestXml->getXML();

        $this->assertInstanceOf("SimpleXMLElement", $xmlObj);
        $this->assertEquals("Test", $xmlObj->getName());

        $xmlString = $requestXml->asXML();

        $this->assertTrue(is_string($xmlString));
        $this->assertSame(0, strpos($xmlString, '<?xml version="1.0" encoding="UTF-8"?>'));
    }


    public function xtestHeader(): void
    {
        $requestXml = $this->createTestRequestXml();
        $xmlObj = $requestXml->getXML();

        // TODO
    }


    public function xtestUser(): void
    {
        $requestXml = $this->createTestRequestXml();
        $xmlObj = $requestXml->getXML();

        // TODO
    }


    public function xtestSoftware(): void
    {
        $requestXml = $this->createTestRequestXml();
        $xmlObj = $requestXml->getXML();

        // TODO
    }
}
