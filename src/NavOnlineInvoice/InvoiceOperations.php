<?php

namespace NavOnlineInvoice;
use Exception;


class InvoiceOperations {

    const MAX_INVOICE_COUNT = 100;
    const COMPRESSION_LEVEL = 1;

    protected $invoices;
    protected $compression;

    /**
     * Az automatikusan felismert technicalAnnulment értéke az első hozzáadott számla alapján.
     * `null` esetén még nincs számla hozzáadva
     *
     * @var bool|null
     */
    protected $detectedTechnicalAnnulment = null;
    protected $index;
    protected $schemaValidation = true;


    /**
     * Számlákat (számla műveleteket) összefogó objektum (collection) készítése
     *
     * @param boolean $compression    gzip tömörítés alkalmazása, részletek: NAV dokumentáció, 1.6.5 Tömörítés és méretkorlát
     */
    function __construct($compression = false) {
        $this->invoices = array();
        $this->index = 1;
        $this->compression = $compression;
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
     * Számla ('szakmai XML') hozzáadása
     *
     * @param \SimpleXMLElement $xml       Számla adatai (szakmai XML)
     * @param string            $operation Számlaművelet Enum(CREATE, MODIFY, STORNO, ANNUL)
     * @return int                      A beszúrt művelet sorszáma (index)
     * @throws \Exception
     */
    public function add(\SimpleXMLElement $xml, $operation = "CREATE") {

        // XSD validálás
        if ($this->schemaValidation) {
            $xsdFile = $operation === "ANNUL" ? Config::getAnnulmentXsdFilename() : Config::getDataXsdFilename();
            Xsd::validate($xml->asXML(), $xsdFile);
        }

        // TODO: ezt esetleg átmozgatni a Reporter vagy ManageInvoiceRequestXml osztályba?
        // Számlák maximum számának ellenőrzése
        if (count($this->invoices) > self::MAX_INVOICE_COUNT) {
            throw new Exception("Maximum " . self::MAX_INVOICE_COUNT . " számlát lehet egyszerre elküldeni!");
        }

        // Technical annulment flag beállítása és ellenőrzése
        $this->detectTechnicalAnnulment($operation);

        $idx = $this->index;
        $this->index++;

        $this->invoices[] = array(
            "index" => $idx,
            "operation" => $operation,
            "invoice" => $this->convertXml($xml)
        );

        return $idx;
    }


    /**
     * A felismert technicalAnnulment értékének lekérdezése.
     * Ha még nem adtunk hozzá számlát, akkor hibát fog dobni.
     *
     * @return bool       technicalAnnulment
     * @throws  Exception
     */
    public function isTechnicalAnnulment() {
        if (!$this->invoices) {
            throw new Exception("Még nincs számla hozzáadva, így a technicalAnnulment értéke nem megállapítható!");
        }

        return $this->detectedTechnicalAnnulment;
    }


    protected function detectTechnicalAnnulment($operation) {
        $currentFlag = ($operation === 'ANNUL');

        // Ha még nincs beállítva, akkor beállítjuk
        if (is_null($this->detectedTechnicalAnnulment)) {
            $this->detectedTechnicalAnnulment = $currentFlag;
        }

        // Ha a korábban beállított nem egyezik az aktuálissal, akkor hiba dobása (NAV nem fogadja el)
        if ($this->detectedTechnicalAnnulment !== $currentFlag) {
            throw new Exception("Az egyszerre feladott számlák nem tartalmazhatnak vegyesen ANNUL, illetve ettől eltérő operation értéket!");
        }
    }


    public function getInvoices() {
        return $this->invoices;
    }


    public function isCompressed() {
        return $this->compression;
    }


    /**
     * XML objektum konvertálása base64-es szöveggé
     * @param \SimpleXMLElement $xml
     * @return string
     */
    protected function convertXml(\SimpleXMLElement $xml) {
        $xml = $xml->asXML();

        if ($this->compression) {
            $xml = gzencode($xml, self::COMPRESSION_LEVEL);
        }

        return base64_encode($xml);
    }


    /**
     * Egy darab számla XML-t átadva visszaad egy InvoiceOperations példányt,
     * amit a Reporter::manageInvoice metódusa fogad paraméterben
     *
     * @param  \SimpleXMLElement $xml
     * @param  string           $operation
     * @param  boolean $compression    gzip tömörítés alkalmazása, részletek: NAV dokumentáció, 1.6.5 Tömörítés és méretkorlát
     * @return InvoiceOperations
     */
    public static function convertFromXml($xml, $operation, $compression = false) {
        $invoiceOperations = new self($compression);
        $invoiceOperations->useDataSchemaValidation();

        $invoiceOperations->add($xml, $operation);

        return $invoiceOperations;
    }


    /**
     * Számla dekódolása (base64 és opcionálisan gzip)
     *
     * @param  string  $base64data
     * @param  boolean $isCompressed
     * @return \SimpleXMLElement
     */
    public static function convertToXml($base64data, $isCompressed = false) {
        $isCompressed = ($isCompressed === true or (string)$isCompressed === 'true');

        $data = base64_decode($base64data);

        if ($isCompressed) {
            $data = gzdecode($data);
        }

        return simplexml_load_string($data);
    }

}
