<?php

use NavOnlineInvoice\QueryTaxpayerRequestXml;


class QueryTaxpayerRequestXmlTest extends BaseTest {

    private function createRequestXmlObject() {
        $taxNumber = "12341234";
        return new QueryTaxpayerRequestXml($this->getConfig(), $taxNumber);
    }


    public function testResponseXml() {
        $requestXml = $this->createRequestXmlObject();
        $xmlObj = $requestXml->getXML();

        $this->assertEquals("12341234", $xmlObj->taxNumber);
        $this->assertEquals("QueryTaxpayerRequest", $xmlObj->getName());
    }


    public function testSchame() {
        $requestXml = $this->createRequestXmlObject();

        $requestXml->validateSchema();
        $this->addToAssertionCount(1);
    }

}
