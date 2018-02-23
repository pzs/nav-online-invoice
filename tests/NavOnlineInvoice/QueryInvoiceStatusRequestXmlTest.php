<?php

use NavOnlineInvoice\QueryInvoiceStatusRequestXml;


class QueryInvoiceStatusRequestXmlTest extends BaseTest {

    private function createRequestXmlObject() {
        $transactionId = "abc123";
        $returnOriginalRequest = true;
        return new QueryInvoiceStatusRequestXml($this->getConfig(), $transactionId, $returnOriginalRequest);
    }


    public function testResponseXml() {
        $requestXml = $this->createRequestXmlObject();
        $xmlObj = $requestXml->getXML();

        $this->assertEquals("abc123", $xmlObj->transactionId);
        $this->assertEquals("1", $xmlObj->returnOriginalRequest);
        $this->assertEquals("QueryInvoiceStatusRequest", $xmlObj->getName());
    }


    public function testSchame() {
        $requestXml = $this->createRequestXmlObject();

        $requestXml->validateSchema();
        $this->addToAssertionCount(1);
    }

}
