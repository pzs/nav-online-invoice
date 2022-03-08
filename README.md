# NAV Online Invoice reporter

> PHP interface for Online Invoice Data Reporting System of Hungarian Tax Office (NAV)

_PHP interfész a NAV Online számla adatszolgáltatásához_

__Letöltés:__
- Composer: [packagist.org/packages/pzs/nav-online-invoice](https://packagist.org/packages/pzs/nav-online-invoice)
- Legfrissebb verzió: [v3.0.2](https://github.com/pzs/nav-online-invoice/releases/tag/v3.0.2) ([zip](https://github.com/pzs/nav-online-invoice/archive/v3.0.2.zip))
- Példa fájlok: [github.com/pzs/nav-online-invoice/tree/master/examples](https://github.com/pzs/nav-online-invoice/tree/master/examples)

## v3.0-ás API támogatás

A modul ezen verziója a NAV v3.0-ás API-ját támogatja. v2.0-ás API támogatáshoz használd a korábbi, [v2.0.6](https://github.com/pzs/nav-online-invoice/releases/tag/v2.0.6) verziót.
Leírást a v3-as modulra való átálláshoz [itt](https://github.com/pzs/nav-online-invoice/releases/tag/v3.0.0) találsz.


***

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
## Tartalom

- [Használat](#haszn%C3%A1lat)
  - [Inicializálás](#inicializ%C3%A1l%C3%A1s)
  - [Adószám ellenőrzése (`queryTaxpayer`)](#ad%C3%B3sz%C3%A1m-ellen%C5%91rz%C3%A9se-querytaxpayer)
  - [Token kérése (`tokenExchange`)](#token-k%C3%A9r%C3%A9se-tokenexchange)
  - [Adatszolgáltatás (`manageInvoice`)](#adatszolg%C3%A1ltat%C3%A1s-manageinvoice)
  - [Technikai érvénytelenítés (`manageAnnulment`)](#technikai-%C3%A9rv%C3%A9nytelen%C3%ADt%C3%A9s-manageannulment)
  - [Státusz lekérdezése (`queryTransactionStatus`)](#st%C3%A1tusz-lek%C3%A9rdez%C3%A9se-querytransactionstatus)
  - [Számla lekérdezése (`queryInvoiceData`)](#sz%C3%A1mla-lek%C3%A9rdez%C3%A9se-queryinvoicedata)
  - [Számla keresése (`queryInvoiceDigest`)](#sz%C3%A1mla-keres%C3%A9se-queryinvoicedigest)
  - [Tranzakciók lekérése (`queryTransactionList`)](#tranzakci%C3%B3k-lek%C3%A9r%C3%A9se-querytransactionlist)
  - [Számlalánc lekérése (`queryInvoiceChainDigest`)](#sz%C3%A1mlal%C3%A1nc-lek%C3%A9r%C3%A9se-queryinvoicechaindigest)
  - [Számla (szakmai) XML validálása küldés nélkül](#sz%C3%A1mla-szakmai-xml-valid%C3%A1l%C3%A1sa-k%C3%BCld%C3%A9s-n%C3%A9lk%C3%BCl)
  - [REST hívás részletei](#rest-h%C3%ADv%C3%A1s-r%C3%A9szletei)
- [Dokumentáció](#dokument%C3%A1ci%C3%B3)
  - [`Config` osztály](#config-oszt%C3%A1ly)
  - [`Reporter` osztály](#reporter-oszt%C3%A1ly)
  - [`InvoiceOperations` osztály](#invoiceoperations-oszt%C3%A1ly)
  - [Exception osztályok](#exception-oszt%C3%A1lyok)
  - [PHP verzió és modulok](#php-verzi%C3%B3-%C3%A9s-modulok)
  - [Linkek](#linkek)
- [További modulok NAV API-hoz](#tov%C3%A1bbi-modulok-nav-api-hoz)
- [License](#license)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Használat

A használathoz a NAV oldalán megfelelő regisztrációt követően létrehozott technikai felhasználó adatainak beállítása szükséges!

### Inicializálás

Technikai felhasználó és szoftver adatok beállítása, Reporter példány létrehozása:

```php
$userData = array(
    "login" => "username",
    "password" => "password",
    // "passwordHash" => "...", // Opcionális, a jelszó már SHA512 hashelt változata. Amennyiben létezik ez a változó, akkor az authentikáció során ezt használja
    "taxNumber" => "12345678",
    "signKey" => "sign-key",
    "exchangeKey" => "exchange-key",
);

$softwareData = array(
    "softwareId" => "123456789123456789",
    "softwareName" => "string",
    "softwareOperation" => "ONLINE_SERVICE",
    "softwareMainVersion" => "string",
    "softwareDevName" => "string",
    "softwareDevContact" => "string",
    "softwareDevCountryCode" => "HU",
    "softwareDevTaxNumber" => "string",
);

$apiUrl = "https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3";

$config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
$config->setCurlTimeout(70); // 70 másodperces cURL timeout (NAV szerver hívásnál), opcionális

// "Connection error. CURL error code: 60" hiba esetén add hozzá a következő sort:
// $config->verifySSL = false;

$reporter = new NavOnlineInvoice\Reporter($config);

```


### Adószám ellenőrzése (`queryTaxpayer`)

A modul automatikusan eltávolítja a namespace-eket a válasz XML-ből (lásd [XML namespace-ek](docs/xml_namespaces.md)), így kényelmesebben használható az XML válasz.

```php
try {
    $result = $reporter->queryTaxpayer("12345678");

    if ($result) {
        print "Az adószám valid.\n";
        print "Az adószámhoz tartozó név: $result->taxpayerName\n";

        print "További lehetséges információk az adózóról:\n";
        print_r($result->taxpayerShortName);
        print_r($result->taxNumberDetail);
        print_r($result->vatGroupMembership);
        print_r($result->taxpayerAddressList);
    } else {
        print "Az adószám nem valid.";
    }

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

```


### Token kérése (`tokenExchange`)

Ezt a metódust célszerű használni a technikai felhasználó adatainak (és a program) tesztelésére is.


```php
try {
    $token = $reporter->tokenExchange();
    print "Token: " . $token;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

```


### Adatszolgáltatás (`manageInvoice`)

Új, módosító és sztornó számla beküldésére.

```php
try {
    // Az $invoiceXml tartalmazza a számla (szakmai) SimpleXMLElement objektumot

    $transactionId = $reporter->manageInvoice($invoiceXml, "CREATE");

    print "Tranzakciós azonosító a státusz lekérdezéshez: " . $transactionId;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

```

Az adatszolgáltatás metódus automatikusan lekéri a tokent is (`tokenExchange`), így ezt nem kell külön megtenni.

Módosító vagy sztornó számlához használd a "MODIFY" és "STORNO" értéket a második paraméterben.

Több számla egyszerre való feladásához lásd a [manageInvoice_multiple.php](examples/manageInvoice_multiple.php) példát.

Elektronikus számlázásról (`electronicInvoiceHash` és `completenessIndicator`) lásd a [manageInvoice_electronic_invoice.php](examples/manageInvoice_electronic_invoice.php) példafájlt és az [Elektronikus számlázás támogatása](docs/electronic_invoice.md) leírást.

:information_source: _Oké, beküldtem a számlát, de mit csináljak Exception esetén?_ :interrobang:

- Ha `NavOnlineInvoice\XsdValidationError` Exception-t kaptál, akkor valamelyik XML-ben lesz hiba! Lehet a szakmai (számla) XML hibás (bár ezt már számlakészítéskor is célszerű ellenőrizni), de a boríték XML is lehet hibás (pl. megadtad a software adatokat, de rossz a formátuma). Fontos megjegyezni, hogy ez az Exception még a küldés előtt jön a nav-online-invoice által generálva.
- Ha `NavOnlineInvoice\CurlError` vagy `NavOnlineInvoice\HttpResponseError` Exception-t kaptál, akkor mindenképp próbáld újraküldeni a számlát pár perc múlva, mert lehet csak épp nincs interneted, vagy a NAV szervere nem elérhető/furcsaságokat válaszol.
- Ha `NavOnlineInvoice\GeneralErrorResponse` vagy `NavOnlineInvoice\GeneralExceptionResponse` az Exception, akkor a NAV válaszolt egy XML üzenettel, viszont ebben - az Exception típusának megfelelő típusú - hibaüzenet volt. Mind a kettő exception esetén az errorCode az $ex->getErrorCode() metódussal lekérhető, melyek értelmezését megtaláljuk a NAV által kiadott interfész specifikációban. Ugyan nem minden hibakód esetén, de az esetek többségében itt is érdemes próbálkozni az újraküldéssel.
- Más egyéb Exception esetén (`NavOnlineInvoice\GeneralExceptionResponse`, `NavOnlineInvoice\GeneralErrorResponse` és `\Exception`) valószínűleg felesleges az újra próbálkozás, naplózd és ellenőrizd a hibaüzenetet (`$ex->getMessage()`)!


### Technikai érvénytelenítés (`manageAnnulment`)

Technikai érvénytelenítés beküldése.

```php
try {
    // Az $annulmentXml tartalmazza a technikai érvénytelenítést tartalmazó SimpleXMLElement objektumot

    $transactionId = $reporter->manageAnnulment($annulmentXml);

    print "Tranzakciós azonosító a státusz lekérdezéshez: " . $transactionId;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

```


### Státusz lekérdezése (`queryTransactionStatus`)

Státusz lekérdezése a `manageInvoice` és `manageAnnulment` operációkhoz.

```php
try {
    $transactionId = "...";
    $statusXml = $reporter->queryTransactionStatus($transactionId);

    print "Válasz XML objektum:\n";
    var_dump($statusXml);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

```

> Az adatszolgáltatás addig nem tekinthető teljesítettnek, amíg a kliens az aszinkronfeldolgozás sikerességéről meg nem győződött és az adott számlához tartozó nyugtaüzenetet meg nem kapta. _(NAV dokumentáció, [#54](https://github.com/pzs/nav-online-invoice/issues/54))_


### Számla lekérdezése (`queryInvoiceData`)

Számla lekérdezése számlaszám alapján, mely kiállító és vevő oldalról is használható. Második paraméterben `true`-t átadva a metódus a dekódolt számla XML-t adja vissza `invoiceDataResult` helyett.

```php
try {
    $invoiceNumberQuery = [
        "invoiceNumber" => "T20190001",
        "invoiceDirection" => "OUTBOUND",
    ];

    // Lekérdezés
    $invoiceDataResult = $reporter->queryInvoiceData($invoiceNumberQuery);

    print "Query results XML elem:\n";
    var_dump($invoiceDataResult);

    // Számla kézi dekódolása
    $invoice = NavOnlineInvoice\InvoiceOperations::convertToXml($invoiceDataResult->invoiceData, $invoiceDataResult->compressedContentIndicator);

    // Számla:
    var_dump($invoice);

    // *** VAGY ***

    // Lekérdezés és számla automatikus dekódolása
    $invoice = $reporter->queryInvoiceData($invoiceNumberQuery, true); // 2. paraméter jelzi az automatikus dekódolást

    // Számla:
    var_dump($invoice);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

```


### Számla keresése (`queryInvoiceDigest`)

Lekérdező operáció, mely kiállító és vevő oldalról is használható.

```php
try {
    $invoiceQueryParams = [
        "mandatoryQueryParams" => [
            "invoiceIssueDate" => [
                "dateFrom" => "2021-01-01",
                "dateTo" => "2021-01-11",
            ],
        ],
        "relationalQueryParams" => [
            "invoiceDelivery" => [
                "queryOperator" => "GTE",
                "queryValue" => "2021-01-01",
            ],
            // Több feltétel esetén ugyanazon elemhez tömbben adjuk át a gyerek elemeket
            "paymentDate" => [
                [
                    "queryOperator" => "GTE",
                    "queryValue" => "2021-01-01",
                ],
                [
                    "queryOperator" => "LTE",
                    "queryValue" => "2021-01-28",
                ],
            ],
        ],
    ];

    $page = 1;

    $invoiceDigestResult = $reporter->queryInvoiceDigest($invoiceQueryParams, $page, "OUTBOUND");

    print "Query results XML elem:\n";
    var_dump($invoiceDigestResult);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

```


### Tranzakciók lekérése (`queryTransactionList`)

A kérésben megadott időintervallumban, a technikai felhasználóhoz tartozó adószámhoz beküldött számlaadat-szolgáltatások listázására szolgál.

```php
try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $insDate = [
        "dateTimeFrom" => "2020-03-01T06:00:00Z",
        "dateTimeTo" => "2020-03-05T18:00:00Z",
    ];
    $page = 1;

    $transactionListResult = $reporter->queryTransactionList($insDate, $page);

    print "Result:\n";
    print_r($transactionListResult);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
```


### Számlalánc lekérése (`queryInvoiceChainDigest`)

A `queryInvoiceChainDigest` egy számlaszám alapján működő lekérdező operáció, amely a számlán szereplő kiállító és a vevő oldaláról is használható. Az operáció a megadott keresőfeltételeknek megfelelő, lapozható számlalistát ad vissza a válaszban. A lista elemei a megadott alapszámlához tartozó számlalánc elemei. A válasz nem tartalmazza a számlák összes üzleti adatát, hanem csak egy kivonatot (digest-et), elsősorban a módosításra és tételsorok számára vonatkozóan.

```php
try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $invoiceChainQuery = [
        "invoiceNumber" => "SZML-123",
        "invoiceDirection" => "OUTBOUND", // OUTBOUND or INBOUND
        "taxNumber" => "12345678", // optional
    ];
    $page = 1;

    $invoiceChainDigestResult = $reporter->queryInvoiceChainDigest($invoiceChainQuery, $page);

    print "Result:\n";
    print_r($invoiceChainDigestResult);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
```


### Számla (szakmai) XML validálása küldés nélkül


```php
// Az $invoiceXml tartalmazza a számla (szakmai) SimpleXMLElement objektumot

$errorMsg = NavOnlineInvoice\Reporter::getInvoiceValidationError($invoiceXml);

if ($errorMsg) {
    print "A számla nem valid, hibaüzenet: " . $errorMsg;
} else {
    print "A számla valid.";
}

```

Számla validálásának másik módját lásd a [validateInvoices.php](examples/validateInvoices.php) példában.


### REST hívás részletei

A REST hívások naplózása és hibakeresés végett lehetőség van az utolsó REST hívás adatainak lekérésére:

```php
// Bármilyen operáció után, pl.:
// $reporter->manageInvoice($invoiceXml, "CREATE");
// hívható (Exception esetén is):

$data = $reporter->getLastRequestData();

print "<br /><br />Request URL: " . htmlspecialchars($data['requestUrl']);
print "<br /><br />Request body: " . htmlspecialchars($data['requestBody']);
print "<br /><br />Response body: " . htmlspecialchars($data['responseBody']);
print "<br /><br />Request ID: " . htmlspecialchars($data['requestId']);
```

A `requestBody` ezen modul által összeállított XML string-et tartalmazza, a `responseBody` pedig a NAV által visszaadott üzenetet, mely az esetek többségében egy XML string.

***

## Dokumentáció

### `Config` osztály

`Config` példány létrehozásakor a paraméterek megadása kötelező:

- `$baseUrl` tipikusan a következő:
    - teszt környezetben: `https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3`
    - éles környezetben: `https://api.onlineszamla.nav.gov.hu/invoiceService/v3`
- `$user` array tartalmazza a NAV oldalán létrehozott technikai felhasználó adatait.
- `$software` array tartalmazza a számlázó szoftver adatait. 2.0-ás verziótól ennek megadása kötelező, formátumát pedig a NAV által kiadott XSD biztosítja.

A `$user` és `$software` paraméter lehet 1-1 JSON fájl elérési útvonala is, ahol a JSON fájl tartalmazza a kívánt adatokat.


__Metódusok__

- `__construct(string $baseUrl, $user, $software)`
- `setBaseUrl($baseUrl)`
- `useApiSchemaValidation([$flag = true])`: NAV szerverrel való kommunikáció előtt a kéréseket (envelop XML) validálja az XSD-vel. A példány alapértelmezett értéke szerint a validáció be van kapcsolva.
- `setSoftware($data)`
- `loadSoftware($jsonFile)`
- `setUser($data)`
- `loadUser($jsonFile)`
- `setCurlTimeout($timeoutSeconds)`: NAV szerver hívásánál (cURL hívás) timeout értéke másodpercben. Alapértelmezetten nincs timeout beállítva. Megjegyzés: manageInvoice hívásnál 2 szerver hívás is történik (token kérés és számlák beküldése), itt külön-külön kell érteni a timeout-ot.
- `setRequestIdGenerator(RequestIdGeneratorInterface $obj)`: opcionálisan egyedi request id generátor állítható be.


### `Reporter` osztály

A `Reporter` osztály példányosításakor egyetlen paraméterben a korábban létrehozott `Config` példányt kell átadni.

Ezen az osztályon érhetjük el a NAV interfészén biztosított szolgáltatásokat. A metódusok nevei megegyeznek a NAV által biztosított specifikációban szereplő operáció nevekkel.


- `__construct(Config $config)`
- `manageInvoice($invoiceOperationsOrXml [, $operation])`: A számla beküldésére szolgáló operáció. Visszatérési értékként a `transactionId`-t adja vissza string-ként. Paraméterben a beküldendő számla XML-t kell átadni, illetve a hozzá tartozó műveletet (ManageInvocieOperationType): CREATE, MODIFY, STORNO. Átadható egyszerre több számla is, ilyenkor első paraméterben InvoiceOperations példányt kell átadni (második paraméternek nincs szerepe ilyenkor).
- `manageAnnulment($invoiceOperationsOrXml)`: Technikai érvénytelenítés beküldésére szolgáló operáció. Paraméterben a technikai érvénytelenítést leíró XML-t, vagy egy InvoiceOperations példányt kell átadni. Utóbbi esetben az InvoiceOperations példány több XML-t is tartalmazhat. A metódus visszaadja a transactionId-t, mellyel lekérdezhető a tranzakció eredménye.
- `queryInvoiceData($invoiceNumberQuery [, $returnDecodedInvoiceData = false])`: Számla lekérdezése számlaszám alapján, mely kiállító és vevő oldalról is használható. Paraméterben az invoiceNumberQuery-nek megfelelően összeállított lekérdezési adatokat kell átadni (`SimpleXMLElement` példány). Visszatérési értéke a visszakapott XML `invoiceDataResult` része (`SimpleXMLElement` példány), vagy a számla XML, ha 2. paraméterben `true`-t adtunk át.
- `queryInvoiceDigest($invoiceQueryParams, $page = 1, $direction = "OUTBOUND")`: Lekérdező operáció, mely kiállító és vevő oldalról is használható. Paraméterben az invoiceQueryParams-nak megfelelően összeállított lekérdezési adatokat kell átadni (SimpleXMLElement), az oldalszámot és a keresés irányát (OUTBOUND, INBOUND). A válasz XML invoiceDigestResult része.
- `queryTransactionStatus(string $transactionId [, $returnOriginalRequest = false])`: A számla adatszolgáltatás feldolgozás aktuális állapotának és eredményének lekérdezésére szolgáló operáció
- `queryTransactionList($insDate [, $page = 1])`: A kérésben megadott időintervallumban, a technikai felhasználóhoz tartozó adószámhoz beküldött számlaadat-szolgáltatások listázására szolgál
- `queryInvoiceChainDigest($invoiceChainQuery [, $page = 1])`
- `queryTaxpayer(string $taxNumber)`: Belföldi adószám validáló és címadat lekérdező operáció. Visszatérési éréke lehet `null` nem létező adószám esetén, `false` érvénytelen adószám esetén, vagy TaxpayerDataType XML elem név és címadatokkal valid adószám esetén
- `tokenExchange()`: Token kérése manageInvoice művelethez (közvetlen használata nem szükséges, viszont lehet használni, mint teszt hívás). Visszatérési értékként a dekódolt tokent adja vissza string-ként.
- `getLastRequestData()`: Utolsó REST hívás adatainak lekérdezése naplózási és hibakeresési céllal. A visszaadott array a következő elemeket tartalmazza: requestUrl, requestBody, responseBody és requestId. Megjegyzés: bizonyos műveletek (manageAnnulment és manageInvoice) kettő REST hívást is indítanak, a tokenExchange hívást, illetve magát az adatküldést. Sikeres hívás esetén csak a tényleges adatküldés eredménye érhető el, Exception esetén pedig mindig az utolsó hívás adata.
- `getLastResponseXml()`: Utolsó válasz XML lekérdezése (operáció hívása után)


### `InvoiceOperations` osztály

`manageInvoice` és `manageAnnulment` híváshoz használandó collection, melyhez a feladni kívánt számlákat lehet hozzáadni. Ez az osztály validálja is az átadott szakmai XML-t az XSD-vel. Elektronikus számlázáshoz lásd [ezt a leírást](docs/electronic_invoice.md).

- `__construct($compression = false)`: compression: gzip tömörítés engedélyezése, részletek: NAV dokumentáció, 1.6.5 Tömörítés és méretkorlát
- `useDataSchemaValidation([$flag = true])`: Számla adat hozzáadásakor az XML-t (szakmai XML) validálja az XSD-vel. Alapértelmezetten be van kapcsolva a validáció.
- `add(SimpleXMLElement $xml [, $operation = "CREATE"][, $electronicInvoiceHash = null])`: Számla XML hozzáadása a listához
- `isTechnicalAnnulment()`
- `getInvoices()`


### Exception osztályok

- `XsdValidationError`: XSD séma validáció esetén, ha hiba történt (a requestXML-ben vagy szakmai XML-ben; a válasz XML nincs vizsgálva). Ez az exception a kliens oldali XML ellenőrzéskor keletkezhet még a szerverrel való kommunikáció előtt.
- `CurlError`: cURL hiba esetén, pl. nem tudott csatlakozni a szerverhez (pl. nincs internet, nem elérhető a szerver).
- `HttpResponseError`: Ha nem XML válasz érkezett, vagy nem sikerült azt parse-olni.
- `GeneralExceptionResponse`: NAV által visszaadott hibaüzenet, ha nem sikerült náluk technikailag valamit feldolgozni (lásd NAV-os leírás Hibakezelés fejezetét).
- `GeneralErrorResponse`: NAV által visszaadott hibaüzenet, ha az XML válaszban `GeneralErrorResponse` érkezett, vagy ha a `funcCode !== 'OK'`.


### PHP verzió és modulok

A NavOnlineInvoice modul tesztelve PHP 5.5 és 7.2 alatt.

Szükséges modulok:

- cURL
- OpenSSL
- PHP 7.1.0 alatt SHA3 hash algoritmust implementáló könyvtár, például:
    - [n-other/php-sha3](https://github.com/n-other/php-sha3), MIT license ([packagist](https://packagist.org/packages/n-other/php-sha3)),
    - vagy [desktopd/php-sha3-streamable](https://notabug.org/desktopd/php-sha3-streamable), LGPL 3+ license,


### Linkek

- https://onlineszamla-test.nav.gov.hu/dokumentaciok
- https://onlineszamla-test.nav.gov.hu/
- https://onlineszamla.nav.gov.hu/
- https://github.com/nav-gov-hu/Online-Invoice, kiemelve a [CHANGELOG_2.0](https://github.com/nav-gov-hu/Online-Invoice/blob/master/src/schemas/nav/gov/hu/OSA/CHANGELOG_2.0.md) leírást


## További modulok NAV API-hoz

- NodeJS: https://github.com/angro-kft/nav-connector
- Scala (JVM): https://github.com/enlivensystems/invoicing-hungarian


## License

[MIT](http://opensource.org/licenses/MIT)

Copyright (c) 2018-2021 github.com/pzs

https://github.com/pzs/nav-online-invoice
