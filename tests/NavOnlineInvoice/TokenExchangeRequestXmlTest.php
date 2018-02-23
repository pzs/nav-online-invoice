<?php

use NavOnlineInvoice\TokenExchangeRequestXml;


class TokenExchangeRequestXmlTest extends BaseTest {

    private function createRequestXmlObject() {
        return new TokenExchangeRequestXml($this->getConfig());
    }


    public function testResponseXml() {
        $requestXml = $this->createRequestXmlObject();
        $xmlObj = $requestXml->getXML();

        $this->assertEquals("TokenExchangeRequest", $xmlObj->getName());
    }


    public function testSchame() {
        $requestXml = $this->createRequestXmlObject();

        $requestXml->validateSchema();
        $this->addToAssertionCount(1);
    }

}
