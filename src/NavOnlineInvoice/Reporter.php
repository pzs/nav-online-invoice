<?php

namespace NavOnlineInvoice;
use Exception;


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
     * manageAnnulment operáció (1.8.1 fejezet)
     *
     * A /manageAnnulment operáció a technikai érvénytelenítések beküldésére szolgáló operáció.
     * Technikai érvénytelenítés csak olyan adatszolgáltatásra küldhető, amelynek a befogadása
     * a NAV oldalon DONE státusszal megtörtént.
     *
     * Paraméterben a technikai érvénytelenítést leíró XML-t, vagy egy InvoiceOperations példányt
     * kell átadni. Utóbbi esetben az InvoiceOperations példány több XML-t is tartalmazhat.
     *
     * A metódus visszaadja a transactionId-t, mellyel lekérdezhető a tranzakció eredménye.
     *
     * @param  [type] $invoiceOperationsOrXml $invoiceOperationsOrXml
     * @return [type]                         $transactionId
     */
    public function manageAnnulment($invoiceOperationsOrXml) {

        // Ha nem InvoiceOperations példányt adtak át, akkor azzá konvertáljuk
        if ($invoiceOperationsOrXml instanceof InvoiceOperations) {
            $invoiceOperations = $invoiceOperationsOrXml;
        } else {
            $invoiceOperations = InvoiceOperations::convertFromXml($invoiceOperationsOrXml, "ANNUL");
        }

        if (!$invoiceOperations->isTechnicalAnnulment()) {
            throw new Exception("manageAnnulment() interfészen csak technikai érvénytelenítést lehet beküldeni.");
        }

        $token = $this->tokenExchange();

        $requestXml = new ManageAnnulmentRequestXml($this->config, $invoiceOperations, $token);
        $responseXml = $this->connector->post("/manageAnnulment", $requestXml);

        return (string)$responseXml->transactionId;
    }


    /**
     * manageInvoice operáció (1.8.2 fejezet)
     *
     * A /manageInvoice a számla adatszolgáltatás beküldésére szolgáló operáció, ezen keresztül van lehetőség számla,
     * módosító vagy stornó számla adatszolgáltatást a NAV részére beküldeni.
     *
     * Paraméterben a beküldendő számla XML-t kell átadni, illetve a hozzá tartozó műveletet (ManageInvocieOperationType): CREATE, MODIFY, STORNO
     *
     * Átadható egyszerre több számla is, ilyenkor első paraméterben InvoiceOperations példányt kell átadni (második paraméternek nincs szerepe ilyenkor).
     *
     * A metódus visszaadja a transactionId-t, mellyel lekérdezhető a tranzakció eredménye.
     *
     * @param  InvoiceOperations|\SimpleXMLElement $invoiceOperationsOrXml
     * @param  string                             $operation
     * @return string                             $transactionId
     */
    public function manageInvoice($invoiceOperationsOrXml, $operation = "CREATE") {

        // Ha nem InvoiceOperations példányt adtak át, akkor azzá konvertáljuk
        if ($invoiceOperationsOrXml instanceof InvoiceOperations) {
            $invoiceOperations = $invoiceOperationsOrXml;
        } else {
            $invoiceOperations = InvoiceOperations::convertFromXml($invoiceOperationsOrXml, $operation);
        }

        if ($invoiceOperations->isTechnicalAnnulment()) {
            throw new Exception("Technikai érvénytelenítésre a manageAnnulment() metódust kell használni a 2.0-ás API-tól kezdődően!");
        }

        $token = $this->tokenExchange();

        $requestXml = new ManageInvoiceRequestXml($this->config, $invoiceOperations, $token);
        $responseXml = $this->connector->post("/manageInvoice", $requestXml);

        return (string)$responseXml->transactionId;
    }


    /**
     * queryInvoiceData operáció (1.8.5 fejezet)
     *
     * A /queryInvoiceData egy számlaszám alapján működő lekérdező operáció, amely a számlán szereplő kiállító és a vevő
     * oldaláról is használható. Az operáció a megadott számlaszám teljes adattartalmát adja vissza a válaszban.
     *
     * @param  array             $invoiceNumberQuery     Az invoiceNumberQuery-nek megfelelően összeállított lekérdezési adatok
     * @param  boolean           [$returnDecodedInvoiceData = false]  invoiceDataResult helyett a dekódolt számla XML-t adja vissza a metódus
     * @return \SimpleXMLElement  $invoiceDataResultXml A válasz XML invoiceDataResult része vagy a dekódolt számla XML
     */
    public function queryInvoiceData($invoiceNumberQuery, $returnDecodedInvoiceData = false) {
        $requestXml = new QueryInvoiceDataRequestXml($this->config, $invoiceNumberQuery);
        $responseXml = $this->connector->post("/queryInvoiceData", $requestXml);

        $result = $responseXml->invoiceDataResult;

        if ($returnDecodedInvoiceData) {
            if (empty($result->invoiceData)) {
                return null;
            }
            $isCompressed = $result->compressedContentIndicator;
            return InvoiceOperations::convertToXml($result->invoiceData, $isCompressed);
        }

        return $result;
    }


    /**
     * queryInvoiceDigest operáció (1.8.6 fejezet)
     *
     * A /queryInvoiceDigest üzleti keresőparaméterek alapján működő lekérdező operáció, amely a számlán szereplő
     * kiállító és a vevő oldaláról is használható. Az operáció a megadott keresőfeltételeknek megfelelő, lapozható
     * számla listát ad vissza a válaszban. A válasz nem tartalmazza a számlák összes üzleti adatát, hanem csak egy
     * kivonatot (digest-et). Amennyiben szükség van a listában szereplő valamely számla teljes adattartalmára, úgy
     * azt a számlaszám birtokában a /queryInvoiceData operációban lehet lekérdezni.
     *
     * @param  array             $invoiceQueryParams     Az invoiceQueryParams-nak megfelelően összeállított lekérdezési adatok
     * @param  Int               [$page=1]          Oldalszám (1-től kezdve a számozást)
     * @param  string            [$direction=OUTBOUND]  A keresés iránya, a keresés elvégezhető kiállítóként és vevőként is [OUTBOUND, INBOUND]
     * @return \SimpleXMLElement  $queryResultsXml A válasz XML invoiceDigestResult része
     */
    public function queryInvoiceDigest($invoiceQueryParams, $page = 1, $direction = "OUTBOUND") {
        $requestXml = new QueryInvoiceDigestRequestXml($this->config, $invoiceQueryParams, $page, $direction);
        $responseXml = $this->connector->post("/queryInvoiceDigest", $requestXml);

        return $responseXml->invoiceDigestResult;
    }


    /**
     * queryTransactionStatus operáció (1.8.8 fejezet)
     *
     * A /queryTransactionStatus a számla adatszolgáltatás feldolgozás aktuális állapotának és eredményének
     * lekérdezésére szolgáló operáció
     *
     * @param  string  $transactionId
     * @param  boolean $returnOriginalRequest
     * @return \SimpleXMLElement  $responseXml    A teljes visszakapott XML, melyből a 'processingResults' elem releváns
     */
    public function queryTransactionStatus($transactionId, $returnOriginalRequest = false) {
        $requestXml = new QueryTransactionStatusRequestXml($this->config, $transactionId, $returnOriginalRequest);
        $responseXml = $this->connector->post("/queryTransactionStatus", $requestXml);

        return $responseXml;
    }


    /**
     * queryTransactionList operáció
     *
     * A /queryTransactionList a kérésben megadott időintervallumban, a technikai felhasználóhoz tartozó adószámhoz
     * beküldött számlaadat-szolgáltatások listázására szolgál.
     *
     * @param  array   $insDate   DateTimeIntervalParamType-nak megfelelő mezők (lásd example)
     * @param  integer $page
     * @return \SimpleXMLElement  $transactionListResult A válasz XML transactionListResult része
     */
    public function queryTransactionList($insDate, $page = 1) {
        $requestXml = new QueryTransactionListRequestXml($this->config, $insDate, $page);
        $responseXml = $this->connector->post("/queryTransactionList", $requestXml);

        return $responseXml->transactionListResult;
    }


    /**
     * queryInvoiceChainDigest operáció
     *
     * A /queryInvoiceChainDigest egy számlaszám alapján működő lekérdező operáció, amely a számlán szereplő
     * kiállító és a vevő oldaláról is használható. Az operáció a megadott keresőfeltételeknek megfelelő,
     * lapozható számlalistát ad vissza a válaszban. A lista elemei a megadott alapszámlához tartozó számlalánc elemei.
     * A válasz nem tartalmazza a számlák összes üzleti adatát, hanem csak egy kivonatot (digest-et), elsősorban a
     * módosításra és tételsorok számára vonatkozóan
     *
     * @param  Array  $invoiceChainQuery
     * @param  integer $page          Oldalszám
     * @return \SimpleXMLElement  $invoiceChainDigestResult A válasz XML invoiceChainDigestResult része
     */
    public function queryInvoiceChainDigest($invoiceChainQuery, $page = 1) {
        $requestXml = new QueryInvoiceChainDigestRequestXml($this->config, $invoiceChainQuery, $page);
        $responseXml = $this->connector->post("/queryInvoiceChainDigest", $requestXml);

        return $responseXml->invoiceChainDigestResult;
    }


    /**
     * queryTaxpayer operáció (1.8.9 fejezet)
     *
     * A /queryTaxpayer belföldi adószám validáló operáció, mely a számlakiállítás folyamatába építve képes
     * a megadott adószám valódiságáról és érvényességéről a NAV adatbázisa alapján adatot szolgáltatni.
     *
     * @param  string $taxNumber            Adószám, pattern: [0-9]{8}
     * @return bool|\SimpleXMLElement     Nem létező adószám esetén `null`, érvénytelen adószám esetén `false` a visszatérési érték, valid adószám estén
     *                                      pedig a válasz XML taxpayerData része (SimpleXMLElement), mely a nevet és címadatokat tartalmazza.
     */
    public function queryTaxpayer($taxNumber) {
        $requestXml = new QueryTaxpayerRequestXml($this->config, $taxNumber);
        $responseXml = $this->connector->post("/queryTaxpayer", $requestXml);

        // 1.9.8.2 fejezet alapján (QueryTaxpayerResponse) a taxpayerValidity tag csak akkor kerül a válaszba, ha a lekérdezett adószám létezik.
        // Nem létező adószámra csak egy <funcCode>OK</funcCode> kerül visszaadásra (funcCode===OK megléte a Connector-ban ellenőrizve van).
        if (!isset($responseXml->taxpayerValidity)) {
            return null;
        }

        // taxpayerValidity értéke lehet false is, ha az adószám létezik, de nem érvényes
        if (empty($responseXml->taxpayerValidity) or (string)($responseXml->taxpayerValidity) === "false") {
            return false;
        }

        // Az adószám valid, adózó adatainak visszaadása
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


    /**
     * Utolsó REST hívás adatainak lekérdezése naplózási és hibakeresési céllal.
     *
     * A visszaadott array a következő elemeket tartalmazza: requestUrl, requestBody, responseBody, requestId, responseXml
     *
     * Megjegyzés: bizonyos műveletek (manageAnnulment és manageInvoice) kettő REST hívást is indítanak,
     * a tokenExchange hívást, illetve magát az adatküldést. Sikeres hívás esetén csak a tényleges adatküldés
     * eredménye érhető el, Exception esetén pedig mindig az utolsó hívás adata.
     *
     * @return array
     */
    public function getLastRequestData() {
        return $this->connector->getLastRequestData();
    }


    /**
     * Utolsó válasz XML lekérdezése (operáció hívása után)
     *
     * @return \SimpleXMLElement $xml
     */
    public function getLastResponseXml() {
        return $this->connector->getLastResponseXml();
    }


    protected function decodeToken($encodedToken) {
        return Util::aes128_decrypt($encodedToken, $this->config->user["exchangeKey"]);
    }


    /**
     * Paraméterben átadott adat XML-t validálja az XSD-vel és hiba esetén string-ként visszaadja a hibát.
     * Ha nincs hiba, akkor visszatérési érték `null`.
     *
     * @param  \SimpleXMLElement $xml   Számla XML
     * @return null|string             Hibaüzenet, vagy `null`, ha helyes az XML
     */
    public static function getInvoiceValidationError($xml) {
        try {
            Xsd::validate($xml->asXML(), Config::getDataXsdFilename());
        } catch(XsdValidationError $ex) {
            return $ex->getMessage();
        }
        return null;
    }

}
