<?php

namespace NavOnlineInvoice;
use Exception;


class Config {

    public $user;
    public $software;

    public $baseUrl;
    public $verifySLL = false;

    public $validateApiSchema = true;

    public $curlTimeout = null;


    /**
     * NavOnlineInvoice Reporter osztály számára szükséges konfigurációs objektum készítése
     *
     * @param string       $baseUrl  NAV API URL
     * @param array|string $user     User data array vagy json fájlnév
     * @param array|string $software Software data array vagy json fájlnév
     * @throws \Exception
     */
    function __construct($baseUrl, $user, $software = null) {

        if (!$baseUrl) {
            throw new Exception("A baseUrl paraméter megadása kötelező!");
        }

        $this->setBaseUrl($baseUrl);

        if (!$user) {
            throw new Exception("A user paraméter megadása kötelező!");
        }

        if (is_string($user)) {
            $this->loadUser($user);
        } else {
            $this->setUser($user);
        }

        if ($software) {
            if (is_string($software)) {
                $this->loadSoftware($software);
            } else {
                $this->setSoftware($software);
            }
        }
    }


    /**
     * NAV online számla API eléréséhez használt URL
     *
     * Teszt: https://api-test.onlineszamla.nav.gov.hu/invoiceService
     * Éles: https://api.onlineszamla.nav.gov.hu/invoiceService
     *
     * @param string $baseUrl  NAV eléréséhez használt környezet
     */
    public function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }


    /**
     * NAV szerverrel való kommunikáció előtt ellenőrizze az XML adatot az API sémával szemben
     *
     * @param  boolean $flag
     */
    public function useApiSchemaValidation($flag = true) {
        $this->validateApiSchema = $flag;
    }


    /**
     *
     * @param array $data
     */
    public function setSoftware($data) {
        $this->software = $data;
    }


    /**
     *
     * @param  string $jsonFile JSON file name
     */
    public function loadSoftware($jsonFile) {
        $data = $this->loadJsonFile($jsonFile);
        $this->setSoftware($data);
    }


    /**
     *
     * @param array $data
     */
    public function setUser($data) {
        $this->user = $data;
    }


    /**
     *
     * @param  string $jsonFile JSON file name
     */
    public function loadUser($jsonFile) {
        $data = $this->loadJsonFile($jsonFile);
        $this->setUser($data);
    }


    /**
     * JSON fájl betöltése
     *
     * @param  string $jsonFile
     * @return array
     * @throws \Exception
     */
    protected function loadJsonFile($jsonFile) {
        if (!file_exists($jsonFile)) {
            throw new Exception("A megadott json fájl nem létezik: $jsonFile");
        }

        $content = file_get_contents($jsonFile);
        $data = json_decode($content, true);

        if ($data === null) {
            throw new Exception("A megadott json fájlt nem sikerült dekódolni!");
        }

        return $data;
    }


    /**
     * cURL hívásnál timeout beállítása másodpercekben.
     * null vagy 0 esetén nincs explicit timeout beállítás
     *
     * @param null|int $timeoutSeconds
     */
    public function setCurlTimeout($timeoutSeconds) {
        $this->curlTimeout = $timeoutSeconds;
    }


    public static function getDataXsdFilename() {
        return __DIR__ . "/xsd/invoiceData.xsd";
    }


    public static function getApiXsdFilename() {
        return __DIR__ . "/xsd/invoiceApi.xsd";
    }

}
