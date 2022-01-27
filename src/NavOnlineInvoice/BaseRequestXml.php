<?php

namespace NavOnlineInvoice;
use Exception;


abstract class BaseRequestXml {

    protected $rootName;
    protected $config;

    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    protected $requestId;
    protected $timestamp;

    const API_NS = "http://schemas.nav.gov.hu/OSA/3.0/api";
    const COMMON_NS = "http://schemas.nav.gov.hu/NTCA/1.0/common";


    /**
     * Request XML készítése
     *
     * @param Config $config    Konfigurációt tartalmazó objektum
     */
    function __construct($config) {
        $this->config = $config;

        $this->createXml();
    }


    protected function createXml() {
        $this->requestId = $this->config->getRequestIdGenerator()->generate();
        $this->timestamp = $this->getTimestamp();

        $this->createXmlObject();
        $this->addHeader();
        $this->addUser();
        $this->addSoftware();
    }


    /**
     * A kérés kliens oldali időpontja UTC-ben, ezredmásodperccel
     *
     * @return string
     */
    protected function getTimestamp() {
        $now = microtime(true);
        $milliseconds = round(($now - floor($now)) * 1000);
        $milliseconds = min($milliseconds, 999);

        return gmdate("Y-m-d\TH:i:s", (int) $now) . sprintf(".%03dZ", $milliseconds);
    }


    protected function createXmlObject() {
        $this->xml = new \SimpleXMLElement($this->getInitialXmlString());
    }


    protected function getInitialXmlString() {

        if (empty($this->rootName)) {
            throw new Exception("rootName has to be defined!");
        }
        return '<?xml version="1.0" encoding="UTF-8"?><' . $this->rootName . ' xmlns:common="' . self::COMMON_NS . '" xmlns="' . self::API_NS . '"></' . $this->rootName . '>';
    }


    protected function addHeader() {
        $header = $this->xml->addChild("header", null, self::COMMON_NS);

        $header->addChild("requestId", $this->requestId);
        $header->addChild("timestamp", $this->timestamp);
        $header->addChild("requestVersion", "3.0");
        $header->addChild("headerVersion", "1.0");
    }


    protected function addUser() {
        $user = $this->xml->addChild("user", null, self::COMMON_NS);

        $passwordHash = isset($this->config->user["passwordHash"]) ? $this->config->user["passwordHash"] : Util::sha512($this->config->user["password"]);
        $signature = $this->getRequestSignatureHash();

        $user->addChild("login", $this->config->user["login"]);
        $user->addChild("passwordHash", $passwordHash)->addAttribute("cryptoType", "SHA-512");
        $user->addChild("taxNumber", $this->config->user["taxNumber"]);
        $user->addChild("requestSignature", $signature)->addAttribute("cryptoType", "SHA3-512");
    }


    protected function addSoftware() {
        if (!$this->config->software) {
            return;
        }

        $software = $this->xml->addChild("software");

        foreach ($this->config->software as $key => $value) {
            $software->addChild($key, $value);
        }
    }


    /**
     * Aláírás generálása.
     *
     * manageInvoice esetén (ManageInvoiceRequestXml osztályban) ez a metódus felülírandó,
     * mert máshogy kell számolni az értéket (más értékeket is össze kell fűzni).
     *
     * Kapcsolódó fejezet: 1.5 A requestSignature számítása
     */
    protected function getRequestSignatureHash() {
        $string = $this->getRequestSignatureString();
        $hash = Util::sha3_512($string);
        return $hash;
    }


    /**
     * Aláírás hash értékének számításához string-ek összefűzése és visszaadása
     *
     * manageInvoice esetén (ManageInvoiceRequestXml osztályban) ez a metódus felülírandó,
     * mert további értékeket is hozzá kell fűzni még.
     *
     * Kapcsolódó fejezet: 1.5 A requestSignature számítása
     */
    protected function getRequestSignatureString() {
        $string = "";

        // requestId értéke
        $string .= $this->requestId;

        // timestamp tag értéke yyyyMMddHHmmss maszkkal, UTC időben (ezredmásodperc nélkül)
        $string .= preg_replace("/\.\d{3}|\D+/", "", $this->timestamp);

        // technikai felhasználó aláíró kulcsának literál értéke
        $string .= $this->config->user["signKey"];

        return $string;
    }


    /**
     * XML objektum lekérése
     *
     * @return \SimpleXMLElement
     */
    public function getXML() {
        return $this->xml;
    }


    /**
     * XML adat lekérése string-ként
     *
     * @return string
     */
    public function asXML() {
        return $this->xml->asXML();
    }


    /**
     * A request XML-t validálja a NAV által biztosított 'invoiceApi.xsd'-vel.
     * Hiba esetén XsdValidationError exception-t dob.
     */
    public function validateSchema() {
        Xsd::validate($this->asXML(), Config::getApiXsdFilename());
    }


    public function getRequestId() {
        return $this->requestId;
    }

}
