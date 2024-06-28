<?php

use NavOnlineInvoice\BaseRequestXml;


class InvoiceOperationsTest extends BaseTest
{

    public function testValidation1(): void
    {
        $invoices = new NavOnlineInvoice\InvoiceOperations();
        $invoices->useDataSchemaValidation();
        $xml = simplexml_load_file(TEST_DATA_DIR . "invoice1.xml");
        if($xml === false) {
            throw new Exception('Xml simple load failed.');
        }
        $invoices->add($xml);
        $this->addToAssertionCount(1);
    }


    public function testValidation2(): void
    {
        $invoices = new NavOnlineInvoice\InvoiceOperations();

        $this->expectException(NavOnlineInvoice\XsdValidationError::class);
        $xml = simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml");
        if ($xml === false) {
            throw new Exception('Xml simple load failed.');
        }
        $invoices->add($xml);
    }


    public function testValidation3(): void
    {
        $invoices = new NavOnlineInvoice\InvoiceOperations();
        $invoices->useDataSchemaValidation(false);
        $xml = simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml");
        if ($xml === false) {
            throw new Exception('Xml simple load failed.');
        }
        $invoices->add($xml);
        
        $this->addToAssertionCount(1);
    }
}
