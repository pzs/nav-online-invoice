<?php

use NavOnlineInvoice\ManageInvoiceRequestXml;


class ManageInvoiceRequestXmlTest extends BaseTest {

    private function createRequestXmlObject() {
        $invoices = new NavOnlineInvoice\InvoiceOperations($this->getConfig());
        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1.xml"));
        $token = "token-123";

        return new ManageInvoiceRequestXml($this->getConfig(), $invoices, $token);
    }


    public function testResponseXml() {
        $requestXml = $this->createRequestXmlObject();
        $xmlObj = $requestXml->getXML();

        $this->assertEquals("token-123", $xmlObj->exchangeToken);
        $this->assertEquals("ManageInvoiceRequest", $xmlObj->getName());
    }


    public function testSchame() {
        $requestXml = $this->createRequestXmlObject();

        $requestXml->validateSchema();
        $this->addToAssertionCount(1);
    }

}
