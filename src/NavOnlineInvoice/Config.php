<?php

namespace NavOnlineInvoice;

use Exception;


class Config
{

    const TEST_URL = 'https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3';
    const PROD_URL = 'https://api.onlineszamla.nav.gov.hu/invoiceService/v3';

    /** @var array<mixed> */
    public array $user;
    /** @var array<mixed> */
    public array $software;

    public string $baseUrl;
    public bool $verifySSL = true;

    public bool $validateApiSchema = true;

    public ?int $curlTimeout = null;

    /** @var RequestIdGeneratorInterface */
    public RequestIdGeneratorInterface $requestIdGenerator;

    /**
     * NavOnlineInvoice Reporter osztály számára szükséges konfigurációs objektum készítése
     *
     * @param string       $baseUrl  NAV API URL
     * @param array<mixed>|string $user     User data array vagy json fájlnév
     * @param array<mixed>|string $software Software data array vagy json fájlnév
     * @throws \Exception
     */
    public function __construct(string $baseUrl, array|string $user, array|string $software)
    {

        if (!$baseUrl) {
            throw new Exception("A baseUrl paraméter megadása kötelező!");
        }

        if (!$software) {
            throw new Exception("A 2.0-ás API-tól a Software adatok megadása kötelező!");
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

        if (is_string($software)) {
            $this->loadSoftware($software);
        } else {
            $this->setSoftware($software);
        }

        $this->setRequestIdGenerator(new RequestIdGeneratorBasic());
    }


    function setRequestIdGenerator(RequestIdGeneratorInterface $obj): void
    {
        $this->requestIdGenerator = $obj;
    }


    function getRequestIdGenerator(): RequestIdGeneratorInterface
    {
        return $this->requestIdGenerator;
    }


    /**
     * NAV online számla API eléréséhez használt URL
     *
     * Teszt: https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3
     * Éles: https://api.onlineszamla.nav.gov.hu/invoiceService/v3
     *
     * @param string $baseUrl  NAV eléréséhez használt környezet
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }


    /**
     * NAV szerverrel való kommunikáció előtt ellenőrizze az XML adatot az API sémával szemben
     *
     * @param  boolean $flag
     */
    public function useApiSchemaValidation($flag = true): void
    {
        $this->validateApiSchema = $flag;
    }


    /**
     *
     * @param array<mixed> $data
     */
    public function setSoftware(array $data): void
    {
        $this->software = $data;
    }


    /**
     *
     * @param  string $jsonFile JSON file name
     */
    public function loadSoftware(string $jsonFile): void
    {
        $data = $this->loadJsonFile($jsonFile);
        $this->setSoftware($data);
    }


    /**
     * @param array<mixed> $data
     */
    public function setUser(array $data): void
    {
        $this->user = $data;
    }


    /**
     * @param  string $jsonFile JSON file name
     */
    public function loadUser(string $jsonFile): void
    {
        $data = $this->loadJsonFile($jsonFile);
        $this->setUser($data);
    }


    /**
     * JSON fájl betöltése
     *
     * @param  string $jsonFile
     * @return mixed
     * @throws \Exception
     */
    protected function loadJsonFile($jsonFile): mixed
    {
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
    public function setCurlTimeout(?int $timeoutSeconds): void
    {
        $this->curlTimeout = $timeoutSeconds;
    }


    public static function getDataXsdFilename(): string
    {
        return __DIR__ . "/xsd/invoiceData.xsd";
    }


    public static function getAnnulmentXsdFilename(): string
    {
        return __DIR__ . "/xsd/invoiceAnnulment.xsd";
    }


    public static function getApiXsdFilename(): string
    {
        return __DIR__ . "/xsd/invoiceApi.xsd";
    }
}
