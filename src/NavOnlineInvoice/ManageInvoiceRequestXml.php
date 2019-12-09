<?php

namespace NavOnlineInvoice;


class ManageInvoiceRequestXml extends BaseRequestXml {

    protected $invoiceOperations;
    protected $token;


    /**
     * @param Config $config
     * @param InvoiceOperations $invoiceOperations
     * @param string $token
     */
    function __construct($config, $invoiceOperations, $token) {
        $this->invoiceOperations = $invoiceOperations;
        $this->token = $token;

        parent::__construct("ManageInvoiceRequest", $config);
    }


    protected function createXml() {
        parent::createXml();
        $this->addToken();
        $this->addInvoiceOperations();
    }


    protected function addToken() {
        $this->xml->addChild("exchangeToken", $this->token);
    }


    protected function addInvoiceOperations() {
        $operationsXml = $this->xml->addChild("invoiceOperations");

        // NOTE: the compression is currently not supported
        $operationsXml->addChild("compressedContent", false);

        // Számlák hozzáadása az XML-hez
        foreach ($this->invoiceOperations->getInvoices() as $invoice) {
            $invoiceXml = $operationsXml->addChild("invoiceOperation");

            $invoiceXml->addChild("index", $invoice["index"]);
            $invoiceXml->addChild("invoiceOperation", $invoice["operation"]);
            $invoiceXml->addChild("invoiceData", $invoice["invoice"]);
        }
    }


    /**
     * Aláírás hash értékének számításához string-ek összefűzése és visszaadása
     *
     * Kapcsolódó fejezet: 1.5 A requestSignature számítása
     */
    protected function getRequestSignatureString() {
        $string = parent::getRequestSignatureString();

        // A számlák hash értékének hozzáfűzése
        foreach ($this->invoiceOperations->getInvoices() as $invoice) {
            $string .= Util::sha3_512($invoice["operation"] . $invoice["invoice"]);
        }

        return $string;
    }

}
