<?php

use NavOnlineInvoice\BaseRequestXml;


class InvoiceOperationsTest extends BaseTest {

    public function testValidation1() {
        $invoices = new NavOnlineInvoice\InvoiceOperations($this->getConfig());

        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1.xml"));
        $this->addToAssertionCount(1);
    }


    /**
     * @expectedException     NavOnlineInvoice\XsdValidationError
     */
    public function testValidation2() {
        $invoices = new NavOnlineInvoice\InvoiceOperations($this->getConfig());

        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml"));
    }


    public function testValidation3() {
        $config = $this->getConfig();
        $config->useDataSchemaValidation(false);
        $invoices = new NavOnlineInvoice\InvoiceOperations($config);

        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml"));
        $this->addToAssertionCount(1);
    }


    /**
     * @expectedException     NavOnlineInvoice\XsdValidationError
     */
    public function testValidation4() {
        $config = $this->getConfig();
        $config->setVersion('1.1');
        $invoices = new NavOnlineInvoice\InvoiceOperations($config);

        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1.xml"));
    }


    public function testValidation5() {
        $config = $this->getConfig();
        $config->setVersion('1.1');
        $invoices = new NavOnlineInvoice\InvoiceOperations($config);

        $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice3.xml"));
        $this->addToAssertionCount(1);
    }

}
