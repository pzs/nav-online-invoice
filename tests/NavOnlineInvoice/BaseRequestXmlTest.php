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


    public function testUserAndSoftwareValuesAreEscaped() {
        $user = array(
            "login" => "login&name",
            "password" => "password",
            "taxNumber" => "11111111",
            "signKey" => "signKey",
            "exchangeKey" => "exchangeKey",
        );
        $software = array(
            "softwareId" => "123456789123456789",
            "softwareName" => "Számlázó <Pro>",
            "softwareDevName" => "Kovács & Társa",
            "softwareDevContact" => "a&amp;b",
        );
        $config = new NavOnlineInvoice\Config(NavOnlineInvoice\Config::TEST_URL, $user, $software);

        $requestXml = new MyBaseRequestXml($config);
        $xml = simplexml_load_string($requestXml->asXML());
        $commonNs = $xml->children(BaseRequestXml::COMMON_NS);

        $this->assertSame("login&name", (string)$commonNs->user->login);
        $this->assertSame("SHA-512", (string)$commonNs->user->passwordHash->attributes()->cryptoType);
        $this->assertSame("SHA3-512", (string)$commonNs->user->requestSignature->attributes()->cryptoType);

        $this->assertSame("Számlázó <Pro>", (string)$xml->software->softwareName);
        $this->assertSame("Kovács & Társa", (string)$xml->software->softwareDevName);
        $this->assertSame("a&amp;b", (string)$xml->software->softwareDevContact);
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
