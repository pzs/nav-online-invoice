<?php

use NavOnlineInvoice\QueryTransactionStatusRequestXml;


class QueryTransactionStatusRequestXmlTest extends BaseTest
{

    private function createRequestXmlObject(): QueryTransactionStatusRequestXml
    {
        $transactionId = "abc123";
        $returnOriginalRequest = true;
        
        return new QueryTransactionStatusRequestXml($this->getConfig(), $transactionId, (string)$returnOriginalRequest);
    }


    public function testResponseXml(): void
    {
        $requestXml = $this->createRequestXmlObject();
        $xmlObj = $requestXml->getXML();

        $this->assertEquals("abc123", $xmlObj->transactionId);
        $this->assertEquals("1", $xmlObj->returnOriginalRequest);
        $this->assertEquals("QueryTransactionStatusRequest", $xmlObj->getName());
    }


    public function testSchame(): void
    {
        $requestXml = $this->createRequestXmlObject();

        $requestXml->validateSchema();
        $this->addToAssertionCount(1);
    }
}
