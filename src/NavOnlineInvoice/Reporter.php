<?php

namespace NavOnlineInvoice;

class Reporter {

    protected $connector;
    protected $config;


    /**
     *
     *
     * @param Config $config    Config object (felhasználó adatok, szoftver adatok, URL, stb.)
     */
    function __construct($config) {
        $this->config = $config;
        $this->connector = new Connector($config);
    }


    /**
     * manageInvoice operáció (1.9.1 fejezet)
     *
     * A /manageInvoice a számla adatszolgáltatás beküldésére szolgáló operáció, ezen keresztül van
     * lehetőség számla, módosító vagy stornó számla adatszolgáltatást, illetve ezek technikai javításait a
     * NAV részére beküldeni.
     *
     * @param  InvoiceOperations $invoiceOperations
     * @return string            $transactionId
     */
    public function manageInvoice($invoiceOperations) {
        $token = $this->tokenExchange();

        $requestXml = new ManageInvoiceRequestXml($this->config, $invoiceOperations, $token);
        $responseXml = $this->connector->post("/manageInvoice", $requestXml);

        return (string)$responseXml->transactionId;
    }


    /**
     * queryInvoiceData operáció (1.9.2 fejezet)
     *
     * A /queryInvoiceData a számla adatszolgáltatások lekérdezésére szolgáló operáció. A lekérdezés
     * történhet konkrét számla sorszámra, vagy lekérdezési paraméterek alapján.
     *
     * @param  string            $queryType     A queryType értéke lehet 'invoiceQuery' vagy 'queryParams'
     *                                          függően attól, hogy konktér számla sorszámot, vagy általános
     *                                          lekérdezési paramétereket adunk át.
     * @param  array             $queryData     A queryType-nak megfelelően összeállított lekérdezési adatok
     * @param  Int               $page          Oldalszám (1-től kezdve a számozást)
     * @return \SimpleXMLElement  $responseXml   A teljes visszakapott XML, melyből a 'queryResults' elem releváns
     */
    public function queryInvoiceData($queryType, $queryData, $page = 1) {
        $requestXml = new QueryInvoiceDataRequestXml($this->config, $queryType, $queryData, $page);
        $responseXml = $this->connector->post("/queryInvoiceStatus", $requestXml);

        return $responseXml;
    }


    /**
     * queryInvoiceStatus operáció (1.9.3 fejezet)
     *
     * A /queryInvoiceStatus a számla adatszolgáltatás feldolgozás aktuális állapotának és eredményének
     * lekérdezésére szolgáló operáció.
     *
     * @param  string  $transactionId
     * @param  boolean $returnOriginalRequest
     * @return \SimpleXMLElement  $responseXml    A teljes visszakapott XML, melyből a 'processingResults' elem releváns
     */
    public function queryInvoiceStatus($transactionId, $returnOriginalRequest = false) {
        $requestXml = new QueryInvoiceStatusRequestXml($this->config, $transactionId, $returnOriginalRequest);
        $responseXml = $this->connector->post("/queryInvoiceStatus", $requestXml);

        return $responseXml;
    }


    /**
     * queryTaxpayer operáció (1.9.4 fejezet)
     *
     * A /queryTaxpayer belföldi adószám validáló operáció, mely a számlakiállítás folyamatába építve képes
     * a megadott adószám valódiságáról és érvényességéről a NAV adatbázisa alapján adatot szolgáltatni.
     *
     * @param  string $taxNumber            Adószám, pattern: [0-9]{8}
     * @return Boolean|\SimpleXMLElement     Invalid adószám esetén `false` a visszatérési érték, valid adószám estén
     *                                      pedig a válasz XML taxpayerData része (SimpleXMLElement), mely a nevet és címadatokat tartalmazza
     */
    public function queryTaxpayer($taxNumber) {
        $requestXml = new QueryTaxpayerRequestXml($this->config, $taxNumber);
        $responseXml = $this->connector->post("/queryTaxpayer", $requestXml);

        // 1.9.4.2 fejezet alapján (QueryTaxpayerResponse) a taxpayerValidity tag csak akkor kerül a válaszba, ha a lekérdezett adószám létezik.
        // Nem létező adószámra csak egy <funcCode>OK</funcCode> kerül visszaadásra (funcCode===OK megléte a Connector-ban ellenőrizve van).
        if (!isset($responseXml->taxpayerValidity)) {
            return false;
        }

        // Az adószám valid, címadatok visszaadása
        return $responseXml->taxpayerData;
    }


    /**
     * Token kérése manageInvoice művelethez.
     *
     * Ezt a metódust lehet használni tesztelésre is, hogy a megadott felhasználói adatok helyesek-e/a NAV szervere visszatér-e valami válasszal.
     *
     * Megjegyzés: csak a token kerül visszaadásra, az érvényességi idő nem. Ennek oka, hogy a tokent csak egy kéréshez (egyszer) lehet használni
     * NAV fórumon elhangzottak alapján (megerősítés szükséges!), és ez az egyszeri felhasználás azonnal megtörténik a token lekérése után (manageInvoice hívás).
     *
     * @return string       Token
     */
    public function tokenExchange() {
        $requestXml = new TokenExchangeRequestXml($this->config);
        $responseXml = $this->connector->post("/tokenExchange", $requestXml);

        $encodedToken = (string)$responseXml->encodedExchangeToken;
        $token = $this->decodeToken($encodedToken);

        return $token;
    }


    protected function decodeToken($encodedToken) {
        return Util::aes128_decrypt($encodedToken, $this->config->user["exchangeKey"]);
    }

}
