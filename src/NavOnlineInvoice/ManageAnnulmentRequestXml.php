<?php

namespace NavOnlineInvoice;


class ManageAnnulmentRequestXml extends ManageInvoiceRequestXml {

    protected $rootName = "ManageAnnulmentRequest";


    protected function addInvoiceOperations() {
        $operationsXml = $this->xml->addChild("annulmentOperations");

        // Számlák hozzáadása az XML-hez
        foreach ($this->invoiceOperations->getInvoices() as $invoice) {
            $invoiceXml = $operationsXml->addChild("annulmentOperation");

            $invoiceXml->addChild("index", $invoice["index"]);
            $invoiceXml->addChild("annulmentOperation", $invoice["operation"]);
            $invoiceXml->addChild("invoiceAnnulment", $invoice["invoice"]);
        }
    }

}
