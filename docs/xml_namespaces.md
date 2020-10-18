
# XML namespace-ek

Ha PHP-ban te is [SimpleXML](https://www.php.net/manual/en/book.simplexml.php)-t használsz, akkor itt találsz egy kis segítséget az XML-en belüli namespace-ekhez.


## Namespace-ek az API XML-ekben

A kérésben szereplő XML összeállítását a `nav-online-invoice` modul végzi, így az itt lévő namespace-ek nem érintenek téged.

A válaszban lévő namespace-eket pedig automatikusan eltávolítja a modul, így a válasz $xml-t továbbra is ugyan úgy használhatod, mint a 2.0-ás API-nál.

Példa XML, ami az API-n keresztül érkezik vissza _nyers_ adat (`queryTaxpayer` válasz):

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ns2:QueryTaxpayerResponse xmlns:ns2="http://schemas.nav.gov.hu/OSA/3.0/api" xmlns="http://schemas.nav.gov.hu/NTCA/1.0/common" xmlns:ns3="http://schemas.nav.gov.hu/OSA/3.0/base" xmlns:ns4="http://schemas.nav.gov.hu/OSA/3.0/data">
  <header>
    <requestId>RID082604200000000000000000</requestId>
    <timestamp>2020-10-18T15:00:00.000Z</timestamp>
    <requestVersion>3.0</requestVersion>
    <headerVersion>1.0</headerVersion>
  </header>
  <result>
    <funcCode>OK</funcCode>
  </result>
  <ns2:software>
    <ns2:softwareId>123456789123456789</ns2:softwareId>
    <ns2:softwareName>string</ns2:softwareName>
    <ns2:softwareOperation>ONLINE_SERVICE</ns2:softwareOperation>
    <ns2:softwareMainVersion>string</ns2:softwareMainVersion>
    <ns2:softwareDevName>string</ns2:softwareDevName>
    <ns2:softwareDevContact>string</ns2:softwareDevContact>
    <ns2:softwareDevCountryCode>HU</ns2:softwareDevCountryCode>
    <ns2:softwareDevTaxNumber>string</ns2:softwareDevTaxNumber>
  </ns2:software>
  <ns2:infoDate>2010-12-31T23:00:00.000Z</ns2:infoDate>
  <ns2:taxpayerValidity>true</ns2:taxpayerValidity>
  <ns2:taxpayerData>
    <ns2:taxpayerName>NEMZETI ADÓ- ÉS VÁMHIVATAL</ns2:taxpayerName>
    <ns2:taxNumberDetail>
      <ns3:taxpayerId>15789934</ns3:taxpayerId>
      <ns3:vatCode>2</ns3:vatCode>
    </ns2:taxNumberDetail>
    <ns2:incorporation>ORGANIZATION</ns2:incorporation>
    <ns2:taxpayerAddressList>
      <ns2:taxpayerAddressItem>
        <ns2:taxpayerAddressType>HQ</ns2:taxpayerAddressType>
        <ns2:taxpayerAddress>
          <ns3:countryCode>HU</ns3:countryCode>
          <ns3:postalCode>1054</ns3:postalCode>
          <ns3:city>BUDAPEST</ns3:city>
          <ns3:streetName>SZÉCHENYI</ns3:streetName>
          <ns3:publicPlaceCategory>UTCA</ns3:publicPlaceCategory>
          <ns3:number>2</ns3:number>
        </ns2:taxpayerAddress>
      </ns2:taxpayerAddressItem>
    </ns2:taxpayerAddressList>
  </ns2:taxpayerData>
</ns2:QueryTaxpayerResponse>
```

És az XML, amit a modul ad vissza namespace-ek eltávolítása után:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<QueryTaxpayerResponse xmlns="http://schemas.nav.gov.hu/NTCA/1.0/common" xmlns:ns2="http://schemas.nav.gov.hu/OSA/3.0/api" xmlns:ns3="http://schemas.nav.gov.hu/OSA/3.0/base" xmlns:ns4="http://schemas.nav.gov.hu/OSA/3.0/data">
  <header>
    <requestId>RID038495400000000000000000</requestId>
    <timestamp>2020-10-18T15:00:00.000Z</timestamp>
    <requestVersion>3.0</requestVersion>
    <headerVersion>1.0</headerVersion>
  </header>
  <result>
    <funcCode>OK</funcCode>
  </result>
  <software>
    <softwareId>123456789123456789</softwareId>
    <softwareName>string</softwareName>
    <softwareOperation>ONLINE_SERVICE</softwareOperation>
    <softwareMainVersion>string</softwareMainVersion>
    <softwareDevName>string</softwareDevName>
    <softwareDevContact>string</softwareDevContact>
    <softwareDevCountryCode>HU</softwareDevCountryCode>
    <softwareDevTaxNumber>string</softwareDevTaxNumber>
  </software>
  <infoDate>2010-12-31T23:00:00.000Z</infoDate>
  <taxpayerValidity>true</taxpayerValidity>
  <taxpayerData>
    <taxpayerName>NEMZETI ADÓ- ÉS VÁMHIVATAL</taxpayerName>
    <taxNumberDetail>
      <taxpayerId>15789934</taxpayerId>
      <vatCode>2</vatCode>
    </taxNumberDetail>
    <incorporation>ORGANIZATION</incorporation>
    <taxpayerAddressList>
      <taxpayerAddressItem>
        <taxpayerAddressType>HQ</taxpayerAddressType>
        <taxpayerAddress>
          <countryCode>HU</countryCode>
          <postalCode>1054</postalCode>
          <city>BUDAPEST</city>
          <streetName>SZÉCHENYI</streetName>
          <publicPlaceCategory>UTCA</publicPlaceCategory>
          <number>2</number>
        </taxpayerAddress>
      </taxpayerAddressItem>
    </taxpayerAddressList>
  </taxpayerData>
</QueryTaxpayerResponse>
```

Amennyiben mégis a nyers XML-lel szeretnél dolgozni, úgy azt lekérhetek a művelet meghívása után a következő metódussal:

```php
$reporter->queryTaxpayer("12345678");
$xmlString = $reporter->getLastRequestData()['responseBody'];
```

A namespace-ek nélküli, teljes XML válasz pedig így kérdezhető le:
```php
$reporter->queryTaxpayer("12345678");
$xml = $reporter->getLastResponseXml();
```

Megjegyezném, hogy a legtöbb metódus a `Reporter` osztályon belül már csak a "lényeget" adja vissza, pl. közvetlen a `transactionId`-t, vagy a válasz XML releváns részét.


## Namespace-ek az adat (számla) XML-ben

A számla XML-hez a modul nem nyúl hozzá, így ezt neked kell helyesen összeállítanod, illetve visszakapott számla XML-t (pl. `queryInvoiceData()` hívás esetén) feldolgoznod.

Pl. `supplierAddress`-en belül `detailedAddress` hozzáadása, ami a `http://schemas.nav.gov.hu/OSA/3.0/base`-en belül van:


```php

$addressElement = $supplierAddress->addChild("user", null, "http://schemas.nav.gov.hu/OSA/3.0/base");

$addressElement->addChild("countryCode", "HU");
$addressElement->addChild("region", "...");
// ...
```

A számla beküldése előtt a modul automatikusan ellenőrzi az XML-t a XSD sémákkal.



_Ez a leírás itt a jövőben még bővülhet, illetve ha van bármi észrevételed, írd meg egy issue vagy pull request alatt._
