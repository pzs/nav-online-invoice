<?php

use NavOnlineInvoice\BaseRequestXml;

// Note
class MyBaseRequestXml extends BaseRequestXml {
    protected $rootName = "Test";
}


class BaseRequestXmlTest extends BaseTest {


    private function createTestRequestXml() {
        return new MyBaseRequestXml($this->getConfig());
    }


    public function testResponseXml() {
        $requestXml = $this->createTestRequestXml();
        $xmlObj = $requestXml->getXML();

        $this->assertInstanceOf("SimpleXMLElement", $xmlObj);
        $this->assertEquals("Test", $xmlObj->getName());

        $xmlString = $requestXml->asXML();

        $this->assertTrue(is_string($xmlString));
        $this->assertSame(0, strpos($xmlString, '<?xml version="1.0" encoding="UTF-8"?>'));
    }


    public function xtestHeader() {
        $requestXml = $this->createTestRequestXml();
        $xmlObj = $requestXml->getXML();

        // TODO
    }


    public function xtestUser() {
        $requestXml = $this->createTestRequestXml();
        $xmlObj = $requestXml->getXML();

        // TODO
    }


    public function xtestSoftware() {
        $requestXml = $this->createTestRequestXml();
        $xmlObj = $requestXml->getXML();

        // TODO
    }

}
