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

        $operationsXml->addChild("technicalAnnulment", $this->invoiceOperations->getTechnicalAnnulment());

        // NOTE: the compression is currently not supported
        $operationsXml->addChild("compressedContent", false);

        // Számlák hozzáadása az XML-hez
        foreach ($this->invoiceOperations->getInvoices() as $invoice) {
            $invoiceXml = $operationsXml->addChild("invoiceOperation");

            $invoiceXml->addChild("index", $invoice["index"]);
            $invoiceXml->addChild("operation", $invoice["operation"]);
            $invoiceXml->addChild("invoice", $invoice["invoice"]);
        }
    }


    /**
     * Aláírás hash értékének számításához string-ek összefűzése és visszaadása
     *
     * Kapcsolódó fejezet: 1.5 A requestSignature számítása
     */
    protected function getRequestSignatureString() {
        $string = parent::getRequestSignatureString();

        // A számlák CRC32 decimális értékének hozzáfűzése
        foreach ($this->invoiceOperations->getInvoices() as $invoice) {
            $string .= Util::crc32($invoice["invoice"]);
        }

        return $string;
    }

}
