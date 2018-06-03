<?php

namespace NavOnlineInvoice;
use Exception;


class InvoiceOperations {

    const MAX_INVOICE_COUNT = 100;

    public $invoices;
    public $technicalAnnulment = false;
    protected $index;
    protected $schemaValidation = false;


    /**
     * Számlákat (számla műveleteket) összefogó objektum (collection) készítése
     */
    function __construct() {
        $this->invoices = array();
        $this->index = 1;
    }


    /**
     * Számla hozzáadásakor ellenőrizze az XML adatot a DATA sémával szemben
     *
     * @param  boolean $flag
     */
    public function useDataSchemaValidation($flag = true) {
        $this->schemaValidation = $flag;
    }


    /**
     * Technical annulment flag beállítása
     *
     * @param boolean $technicalAnnulment
     */
    public function setTechnicalAnnulment($technicalAnnulment = true) {
        $this->technicalAnnulment = $technicalAnnulment;
    }


    /**
     * Számla ('szakmai XML') hozzáadása
     *
     * @param SimpleXMLElement $xml     Számla adatai (szakmai XML)
     * @param string $operation         Számlaművelet Enum(CREATE, MODIFY, STORNO, ANNUL)
     * @return int                      A beszúrt művelet sorszáma (index)
     */
    public function add($xml, $operation = "CREATE") {

        // XSD validálás
        if ($this->schemaValidation) {
            Xsd::validate($xml->asXML(), Config::getDataXsdFilename());
        }

        // TODO: ezt esetleg átmozgatni a Reporter vagy ManageInvoiceRequestXml osztályba?
        // Számlák maximum számának ellenőrzése
        if (count($this->invoices) > self::MAX_INVOICE_COUNT) {
            throw new Exception("Maximum " . self::MAX_INVOICE_COUNT . " számlát lehet egyszerre elküldeni!");
        }

        $idx = $this->index;
        $this->index++;

        $this->invoices[] = array(
            "index" => $idx,
            "operation" => $operation,
            "invoice" => $this->convertXml($xml)
        );

        return $idx;
    }


    public function getTechnicalAnnulment() {
        return $this->technicalAnnulment;
    }


    public function getInvoices() {
        return $this->invoices;
    }


    /**
     * XML objektum konvertálása base64-es szöveggé
     */
    protected function convertXml($xml) {
        $xml = $xml->asXML();
        return base64_encode($xml);
    }

}
