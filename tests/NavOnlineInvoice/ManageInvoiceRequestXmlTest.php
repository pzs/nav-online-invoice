<?php

use NavOnlineInvoice\ManageInvoiceRequestXml;


class ManageInvoiceRequestXmlTest extends BaseTest
{

    private function createRequestXmlObject(): ManageInvoiceRequestXml
    {
        $invoices = new NavOnlineInvoice\InvoiceOperations();
        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1.xml"));
        $token = "token-123";

        return new ManageInvoiceRequestXml($this->getConfig(), $invoices, $token);
    }


    public function testResponseXml(): void
    {
        $requestXml = $this->createRequestXmlObject();
        $xmlObj = $requestXml->getXML();

        $this->assertEquals("token-123", $xmlObj->exchangeToken);
        $this->assertEquals("ManageInvoiceRequest", $xmlObj->getName());
    }


    public function testSchame(): void
    {
        $requestXml = $this->createRequestXmlObject();

        $requestXml->validateSchema();
        $this->addToAssertionCount(1);
    }
}
