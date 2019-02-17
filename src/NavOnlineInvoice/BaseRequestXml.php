<?php

namespace NavOnlineInvoice;

class BaseRequestXml {

    protected $rootName;
    protected $config;

    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    protected $requestId;
    protected $timestamp;


    /**
     * Request XML készítése
     *
     * @param string $rootName  Root XML elem neve
     * @param Config $config    Konfigurációt tartalmazó objektum
     */
    function __construct($rootName, $config) {
        $this->rootName = $rootName;
        $this->config = $config;

        $this->createXml();
    }


    protected function createXml() {
        $this->requestId = $this->generateRequestId();
        $this->timestamp = $this->getTimestamp();

        $this->createXmlObject();
        $this->addHeader();
        $this->addUser();
        $this->addSoftware();
    }


    /**
     * Egyedi request ID generálása
     *
     * NAV specifikáció:
     * A requestId a kérés azonosítója. Értéke bármi lehet, ami a pattern szerint érvényes és az
     * egyediséget nem sérti. A requestId-nak - az adott adózó vonatkozásában - kérésenként
     * egyedinek kell lennie. Az egyediségbe csak a sikeresen feldolgozott kérések számítanak bele, a
     * sikertelen vagy a szerver által elutasított kérések azonosítói nem, azok az első sikeres
     * tranzakcióig (HTTP 200-as válaszig) újra felhasználhatóak. A tag értéke beleszámít a
     * requestSignature értékébe.
     *
     * Pattern: [+a-zA-Z0-9_]{1,30}
     *
     * @return string
     */
    protected function generateRequestId() {
        $id = "RID" . microtime() . mt_rand(10000, 99999);
        $id = preg_replace("/[^A-Z0-9]/", "", $id);
        $id = substr($id, 0, 30);
        return $id;
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

        return gmdate("Y-m-d\TH:i:s", $now) . sprintf(".%03dZ", $milliseconds);
    }


    protected function createXmlObject() {
        $this->xml = new \SimpleXMLElement($this->getInitialXmlString());
    }


    protected function getInitialXmlString() {
        return '<?xml version="1.0" encoding="UTF-8"?><' . $this->rootName . ' xmlns="http://schemas.nav.gov.hu/OSA/1.0/api"></' . $this->rootName . '>';
    }


    protected function addHeader() {
        $header = $this->xml->addChild("header");

        $header->addChild("requestId", $this->requestId);
        $header->addChild("timestamp", $this->timestamp);
        $header->addChild("requestVersion", "1.1");
        $header->addChild("headerVersion", "1.0");
    }


    protected function addUser() {
        $user = $this->xml->addChild("user");

        $passwordHash = Util::sha512($this->config->user["password"]);
        $signature = $this->getRequestSignatureHash();

        $user->addChild("login", $this->config->user["login"]);
        $user->addChild("passwordHash", $passwordHash);
        $user->addChild("taxNumber", $this->config->user["taxNumber"]);
        $user->addChild("requestSignature", $signature);
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
        $hash = Util::sha512($string);
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

}
