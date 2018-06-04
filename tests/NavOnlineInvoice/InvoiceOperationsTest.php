<?php

use NavOnlineInvoice\BaseRequestXml;


class InvoiceOperationsTest extends BaseTest {

    public function testValidation1() {
        $invoices = new NavOnlineInvoice\InvoiceOperations();
        $invoices->useDataSchemaValidation();

        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1.xml"));
        $this->addToAssertionCount(1);
    }


    /**
     * @expectedException     NavOnlineInvoice\XsdValidationError
     */
    public function testValidation2() {
        $invoices = new NavOnlineInvoice\InvoiceOperations();

        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml"));
    }


    public function testValidation3() {
        $invoices = new NavOnlineInvoice\InvoiceOperations();
        $invoices->useDataSchemaValidation(false);

        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml"));
        $this->addToAssertionCount(1);
    }

}
