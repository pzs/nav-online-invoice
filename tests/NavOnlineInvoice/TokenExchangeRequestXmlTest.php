<?php

use NavOnlineInvoice\TokenExchangeRequestXml;


class TokenExchangeRequestXmlTest extends BaseTest
{

    private function createRequestXmlObject(): TokenExchangeRequestXml
    {
        return new TokenExchangeRequestXml($this->getConfig());
    }


    public function testResponseXml(): void
    {
        $requestXml = $this->createRequestXmlObject();
        $xmlObj = $requestXml->getXML();

        $this->assertEquals("TokenExchangeRequest", $xmlObj->getName());
    }


    public function testSchame(): void
    {
        $requestXml = $this->createRequestXmlObject();

        $requestXml->validateSchema();
        $this->addToAssertionCount(1);
    }
}
