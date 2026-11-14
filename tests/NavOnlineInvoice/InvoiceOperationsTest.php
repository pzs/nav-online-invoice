<?php

use NavOnlineInvoice\BaseRequestXml;


class InvoiceOperationsTest extends BaseTest {

    public function testValidation1() {
        $invoices = new NavOnlineInvoice\InvoiceOperations();
        $invoices->useDataSchemaValidation();

        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1.xml"));
        $this->addToAssertionCount(1);
    }


    public function testValidation2() {
        $invoices = new NavOnlineInvoice\InvoiceOperations();

        $this->expectException(NavOnlineInvoice\XsdValidationError::class);
        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml"));
    }


    public function testValidation3() {
        $invoices = new NavOnlineInvoice\InvoiceOperations();
        $invoices->useDataSchemaValidation(false);

        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml"));
        $this->addToAssertionCount(1);
    }


    public function testMaxInvoiceCount() {
        $invoices = new NavOnlineInvoice\InvoiceOperations();
        $invoices->useDataSchemaValidation(false);
        $xml = simplexml_load_file(TEST_DATA_DIR . "invoice1.xml");

        for ($i = 0; $i < NavOnlineInvoice\InvoiceOperations::MAX_INVOICE_COUNT; $i++) {
            $invoices->add($xml);
        }

        $this->assertCount(NavOnlineInvoice\InvoiceOperations::MAX_INVOICE_COUNT, $invoices->getInvoices());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Maximum " . NavOnlineInvoice\InvoiceOperations::MAX_INVOICE_COUNT . " számlát lehet egyszerre elküldeni!");
        $invoices->add($xml);
    }

}
