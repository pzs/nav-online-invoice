<?php

namespace NavOnlineInvoice;

use Exception;
use SimpleXMLElement;


class InvoiceOperations
{

    const MAX_INVOICE_COUNT = 100;
    const COMPRESSION_LEVEL = 1;

    /** @var array<int,mixed> **/
    protected array $invoices;
    protected bool $compression;

    /**
     * Az automatikusan felismert technicalAnnulment értéke az első hozzáadott számla alapján.
     * `null` esetén még nincs számla hozzáadva
     *
     * @var bool|null
     */
    protected $detectedTechnicalAnnulment = null;
    protected int $index;
    protected bool $schemaValidation = true;


    /**
     * Számlákat (számla műveleteket) összefogó objektum (collection) készítése
     *
     * @param boolean $compression    gzip tömörítés alkalmazása, részletek: NAV dokumentáció, 1.6.5 Tömörítés és méretkorlát
     */
    function __construct(bool $compression = false)
    {
        $this->invoices = array();
        $this->index = 1;
        $this->compression = $compression;
    }


    /**
     * Számla hozzáadásakor ellenőrizze az XML adatot a DATA sémával szemben
     *
     * @param  boolean $flag
     */
    public function useDataSchemaValidation(bool $flag = true): void
    {
        $this->schemaValidation = $flag;
    }


    /**
     * Számla ('szakmai XML') hozzáadása
     *
     * @param \SimpleXMLElement $xml       Számla adatai (szakmai XML)
     * @param string            $operation Számlaművelet Enum(CREATE, MODIFY, STORNO, ANNUL)
     * @param ?string            $electronicInvoiceHash Számla SHA3-512 hash értéke elektronikus számla esetén. Ha completenessIndicator=true, akkor itt null-t kell átadni.
     * @return int                      A beszúrt művelet sorszáma (index)
     * @throws \Exception
     */
    public function add(\SimpleXMLElement $xml, string $operation = "CREATE", ?string $electronicInvoiceHash = null): int
    {

        // XSD validálás
        if ($this->schemaValidation) {
            $xsdFile = $operation === "ANNUL" ? Config::getAnnulmentXsdFilename() : Config::getDataXsdFilename();
            Xsd::validate($xml->asXML(), $xsdFile);
        }

        // Számlák maximum számának ellenőrzése
        if (count($this->invoices) > self::MAX_INVOICE_COUNT) {
            throw new Exception("Maximum " . self::MAX_INVOICE_COUNT . " számlát lehet egyszerre elküldeni!");
        }

        // Technical annulment flag beállítása és ellenőrzése
        $this->detectTechnicalAnnulment($operation);

        $completenessIndicator = $this->isComplete($xml);

        if ($completenessIndicator and $electronicInvoiceHash) {
            throw new Exception("completenessIndicator=true esetén az electronicInvoiceHash értékét a nav-online-invoice modul számolja automatikusan, így ezt a paramétert üresen kell hagyni!");
        }

        $invoiceBase64Data = $this->convertXml($xml);

        if ($completenessIndicator) {
            $electronicInvoiceHash = Util::sha3_512($invoiceBase64Data);
        }

        $idx = $this->index;
        $this->index++;

        $this->invoices[] = array(
            "index" => $idx,
            "operation" => $operation,
            "invoice" => $invoiceBase64Data,
            "electronicInvoiceHash" => $electronicInvoiceHash,
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
    public function isTechnicalAnnulment(): ?bool
    {
        if (!$this->invoices) {
            throw new Exception("Még nincs számla hozzáadva, így a technicalAnnulment értéke nem megállapítható!");
        }

        return $this->detectedTechnicalAnnulment;
    }


    protected function detectTechnicalAnnulment(string $operation): void
    {
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


    /**
     * @return array<int,mixed>
     */
    public function getInvoices(): array
    {
        return $this->invoices;
    }


    /**
     * Utoljára hozzáadott számla electronicInvoiceHash értékét adja vissza.
     * Ez lehet paraméterben átadott, vagy completenessIndicator=true esetén a modul által számolt hash.
     *
     */
    public function getLastInvoiceHash(): ?string
    {
        if (!$this->invoices) {
            return null;
        }

        $lastInvoice = $this->invoices[count($this->invoices) - 1];

        return $lastInvoice['electronicInvoiceHash'];
    }


    public function isCompressed(): bool
    {
        return $this->compression;
    }


    /**
     * XML objektum konvertálása base64-es szöveggé
     * @param \SimpleXMLElement $xml
     * @return string
     */
    protected function convertXml(\SimpleXMLElement $xml): string
    {
        $xml = $xml->asXML();

        if ($this->compression) {
            $xml = gzencode($xml, self::COMPRESSION_LEVEL);
        }

        return base64_encode($xml);
    }


    protected function isComplete(\SimpleXMLElement $xml): bool
    {
        return (string)$xml->completenessIndicator === 'true';
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
    public static function convertFromXml($xml, $operation, $compression = false): self
    {
        $invoiceOperations = new self($compression);
        $invoiceOperations->useDataSchemaValidation();

        $invoiceOperations->add($xml, $operation);

        return $invoiceOperations;
    }


    /**
     * Számla dekódolása (base64 és opcionálisan gzip)
     *
     * @param  string  $base64data
     * @param  bool $isCompressed
     * @return SimpleXMLElement
     */
    public static function convertToXml($base64data, bool $isCompressed = false): SimpleXMLElement|false
    {
        $isCompressed = $isCompressed === true;

        $data = base64_decode($base64data);

        if ($isCompressed) {
            $data = gzdecode($data);
        }

        return simplexml_load_string($data);
    }
}
