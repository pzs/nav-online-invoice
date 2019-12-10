# NAV Online Invoice reporter

> A PHP interface for Online Invoice Data Reporting System of Hungarian Tax Office (NAV)

_PHP interfész a NAV Online számla adatszolgáltatásához_

__Letöltés:__
- Composer: [packagist.org/packages/pzs/nav-online-invoice](https://packagist.org/packages/pzs/nav-online-invoice)
- Legfrissebb verzió: [github.com/pzs/nav-online-invoice/releases/latest](https://github.com/pzs/nav-online-invoice/releases/latest)
- Korábbi verziók: [github.com/pzs/nav-online-invoice/releases](https://github.com/pzs/nav-online-invoice/releases)
- Példa fájlok: [github.com/pzs/nav-online-invoice/tree/master/examples](https://github.com/pzs/nav-online-invoice/tree/master/examples)

NAV Online számla oldala: [onlineszamla.nav.gov.hu](https://onlineszamla.nav.gov.hu/)

## :mega: 2.0-ás API támogatás

Amennyiben a NAV 2.0-ás API-jára meg szeretnéd kezdeni az átállást, kérlek, használd ezen modul [2.0.0-ás RC/pre-release](https://github.com/pzs/nav-online-invoice/releases) verzióját. Frissített leírást és példafájlokat megtalálod a [2.0-ás branch](https://github.com/pzs/nav-online-invoice/tree/2.0) alatt, illetve [packagist](https://packagist.org/packages/pzs/nav-online-invoice)-ről is letölthető a `v2.0.0-RC1` verzió.

:information_source: Az itt következő lenti leírás az 1.1-es modulhoz tartozik.


## Használat

A használathoz a NAV oldalán megfelelő regisztrációt követően létrehozott technikai felhasználó adatainak beállítása szükséges!


### Inicializálás

Technikai felhasználó (és szoftver) adatok beállítása, Reporter példány létrehozása:

```php
$apiUrl = "https://api-test.onlineszamla.nav.gov.hu/invoiceService";
$config = new NavOnlineInvoice\Config($apiUrl, "userData.json");
$config->setCurlTimeout(70); // 70 másodperces cURL timeout (NAV szerver hívásnál), opcionális

$reporter = new NavOnlineInvoice\Reporter($config);

```

Minta JSON fájlok: [userData.json](tests/testdata/userData.sample.json), [softwareData.json](tests/testdata/softwareData.json).
JSON fájl helyett az értékeket tömbben is át lehet adni (lásd lent, Dokumentáció / Config osztály fejezet).
A konstruktor 3. paraméterében a software adatokat is át lehet adni opcionálisan, ez nem kötelező a NAV részéről.

:information_source: A v0.5.0-ás verziótól az API és Data séma validálás alapértelmezetten be van kapcsolva, így küldés előtt az XML-ek séma validálva lesznek.


### Adószám ellenőrzése (`queryTaxpayer`)

```php
try {
    $result = $reporter->queryTaxpayer("12345678");

    if ($result) {
        print "Az adószám valid.\n";
        print "Az adószámhoz tartozó név: " . $result->taxpayerName . "\n";
        if (isset($result->taxpayerAddress)) {
            print "Cím: ";
            print_r($result->taxpayerAddress);
        } else {
            print "Az adószámhoz nem tartozik cím.";
        }
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

Az adatszolgáltatás metódus automatikusan lekéri a tokent is (`tokenExchange`), így ezt nem kell külön megtenni.

```php
try {
    // Az $invoiceXml tartalmazza a számla (szakmai) SimpleXMLElement objektumot

    $transactionId = $reporter->manageInvoice($invoiceXml, "CREATE");

    print "Tranzakciós azonosító a státusz lekérdezéshez: " . $transactionId;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

```

Több számla egyszerre való feladásához lásd a [manageInvoice.php](examples/manageInvoice.php) példát.

:information_source: _Oké, beküldtem a számlát, de mit csináljak Exception esetén?_ :interrobang:

- Ha `NavOnlineInvoice\XsdValidationError` Exception-t kaptál, akkor valamelyik XML-ben lesz hiba! Lehet a szakmai (számla) XML hibás (bár ezt már számlakészítéskor is célszerű ellenőrizni), de a boríték XML is lehet hibás (pl. megadtad a software adatokat, de rossz a formátuma). Fontos megjegyezni, hogy ez az Exception még a küldés előtt jön a nav-online-invoice által generálva.
- Ha `NavOnlineInvoice\CurlError` vagy `NavOnlineInvoice\HttpResponseError` Exception-t kaptál, akkor mindenképp próbáld újraküldeni a számlát pár perc múlva, mert lehet csak épp nincs interneted, vagy a NAV szervere nem elérhető/furcsaságokat válaszol.
- Ha `NavOnlineInvoice\GeneralErrorResponse` vagy `NavOnlineInvoice\GeneralExceptionResponse` az Exception, akkor a NAV válaszolt egy XML üzenettel, viszont ebben - az Exception típusának megfelelő típusú - hibaüzenet volt. Mind a kettő exception esetén az errorCode az $ex->getErrorCode() metódussal lekérhető, melyek értelmezését megtaláljuk a NAV által kiadott interfész specifikációban. Ugyan nem minden hibakód esetén, de az esetek többségében itt is érdemes próbálkozni az újraküldéssel.
- Más egyéb Exception esetén (`NavOnlineInvoice\GeneralExceptionResponse`, `NavOnlineInvoice\GeneralErrorResponse` és `\Exception`) valószínűleg felesleges az újrapróbálkozás, naplózd és ellenőrizd a hibaüzenetet (`$ex->getMessage()`)!


### Státusz lekérdezése (`queryInvoiceStatus`)

Az adatszolgáltatás operációval beküldött számla státuszának lekérdezésére szolgáló operáció. `$transactionId`-nak a `manageInvoice` metódus által visszaadott azonosítót kell megadni.

```php
try {
    $transactionId = "...";
    $statusXml = $reporter->queryInvoiceStatus($transactionId);

    print "Válasz XML objektum:\n";
    var_dump($statusXml);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

```


### Számla adatszolgáltatások lekérdezése (`queryInvoiceData`)

Beküldött számlák lekérdezése/keresése.

```php
try {
    $queryData = [
        "invoiceNumber" => "T20190001",
        "requestAllModification" => true
    ];
    $queryResults = $reporter->queryInvoiceData("invoiceQuery", $queryData);

    print "Query results XML elem:\n";
    var_dump($queryResults);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

```

Lásd a másik példát is: [queryInvoiceData_queryParams.php](examples/queryInvoiceData_queryParams.php).


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


## Dokumentáció

### `Config` osztály

`Config` példány létrehozásakor a `$baseUrl` és a technikai felhasználó adatok (`$user`) megadása kötelező.

`$baseUrl` tipikusan a következő:
- teszt környezetben: `https://api-test.onlineszamla.nav.gov.hu/invoiceService`
- éles környezetben: `https://api.onlineszamla.nav.gov.hu/invoiceService`

Konstruktorban a `$user` paraméter lehet egy JSON fájl neve, vagy egy array, mely a következő mezőket tartalmazza (NAV oldalán létrehozott technikai felhasználó adatai):
- `login`
- `password`
- `taxNumber`
- `signKey`: XML aláírókulcs
- `exchangeKey`: XML cserekulcs

A `$software` adatok megadása _nem kötelező_ a specifikáció alapján. Amennyiben mégis megadjuk, úgy a következő mezőket tartalmazhatja a JSON fájl, vagy az átadott array (figyeljünk, hogy az értékek megfeleljenek az XSD-nek!):

- `softwareId`
- `softwareName`
- `softwareOperation`
- `softwareMainVersion`
- `softwareDevName`
- `softwareDevContact`
- `softwareDevCountryCode`
- `softwareDevTaxNumber`


__Metódusok__

- `__construct(string $baseUrl, $user [, $software = null])`
- `setBaseUrl($baseUrl)`
- `useApiSchemaValidation([$flag = true])`: NAV szerverrel való kommunikáció előtt a kéréseket (envelop XML) validálja az XSD-vel. A példány alapértelmezett értéke szerint a validáció be van kapcsolva.
- `setSoftware($data)`
- `loadSoftware($jsonFile)`
- `setUser($data)`
- `loadUser($jsonFile)`
- `setCurlTimeout($timeoutSeconds)`: NAV szerver hívásánál (cURL hívás) timeout értéke másodpercben. Alapértelmezetten nincs timeout beállítva. Megjegyzés: manageInvoice hívásnál 2 szerver hívás is történik (token kérés és számlák beküldése), itt külön-külön kell érteni a timeout-ot.


### `Reporter` osztály

A `Reporter` osztály példányosításakor egyetlen paraméterben a korábban létrehozott `Config` példányt kell átadni.

Ezen az osztályon érhetjük el a NAV interfészén biztosított szolgáltatásokat. A metódusok nevei megegyeznek a NAV által biztosított specifikációban szereplő operáció nevekkel.


- `__construct(Config $config)`
- `manageInvoice($invoiceOperationsOrXml [, $operation])`: A számla beküldésére szolgáló operáció. Visszatérési értékként a `transactionId`-t adja vissza string-ként. Paraméterben át lehet adni vagy egy darab `SimpleXMLElement` példányt, ami a számlát tartalmazza, vagy egy `InvoiceOperations` példányt, ami több számlát is tartalmazhat. A `technicalAnnulment` flag értéke automatikusan felismert és beállításra kerül az `operation` értékéből. Lásd a példa fájlokat.
    - `SimpleXMLElement` példány (egy számla) átadása esetén a 2., `$operation` paraméterben át kell adnunk a műveletet (lásd `OperationType`-ot a NAV leírásban), mely értékei a következők lehetnek: `"CREATE"` (alapértelmezett), `"MODIFY"`, `"STORNO"`, `"ANNUL"`.
    - `InvoiceOperations` példány esetén maga az átadott példány tartalmazza már a műveletet.
- `queryInvoiceData(string $queryType, array $queryData [, int $page = 1])`: A számla adatszolgáltatások lekérdezésére szolgáló operáció, visszatérési értéke a visszakapott XML `queryResults` része (`SimpleXMLElement` példány)
- `queryInvoiceStatus(string $transactionId [, $returnOriginalRequest = false])`: A számla adatszolgáltatás feldolgozás aktuális állapotának és eredményének lekérdezésére szolgáló operáció
- `queryTaxpayer(string $taxNumber)`: Belföldi adószám validáló és címadat lekérdező operáció. Visszatérési éréke lehet `null` nem létező adószám esetén, `false` érvénytelen adószám esetén, vagy TaxpayerDataType XML elem név és címadatokkal valid adószám esetén
- `tokenExchange()`: Token kérése manageInvoice művelethez (közvetlen használata nem szükséges, viszont lehet használni, mint teszt hívás). Visszatérési értékként a dekódolt tokent adja vissza string-ként.


### `InvoiceOperations` osztály

`manageInvoice` híváshoz használandó collection, melyhez a feladni kívánt számlákat lehet hozzáadni. Ez az osztály opcionálisan validálja is az átadott szakmai XML-t az XSD-vel.

- `__construct()`
- `useDataSchemaValidation([$flag = true])`: Számla adat hozzáadásakor az XML-t (szakmai XML) validálja az XSD-vel. Alapértelmezetten be van kapcsolva a validáció.
- `add(SimpleXMLElement $xml [, $operation = "CREATE"])`: Számla XML hozzáadása a listához
- `getTechnicalAnnulment()`
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


### Linkek

- https://onlineszamla-test.nav.gov.hu/dokumentaciok
- https://onlineszamla-test.nav.gov.hu/
- https://onlineszamla.nav.gov.hu/


## TODO

- További tesztek írása, ami a NAV szerverét is meghívja teszt közben


## License

[MIT](http://opensource.org/licenses/MIT)

Copyright (c) 2018 github.com/pzs

https://github.com/pzs/nav-online-invoice