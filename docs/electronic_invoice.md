
# Elektronikus számlázás támogatása

_A koncepció megértéséhez lásd a NAV specifikáció, 2.6. fejezetét: Elektronikus számlázás támogatása. A lenti leírás csak a `nav-online-invoice` modul használatára terjed ki._


## Példafájl

Lásd: [manageInvoice_electronic_invoice.php](../examples/manageInvoice_electronic_invoice.php)


## completenessIndicator=false esetén

Ebben az esetben a felhasználó képezi a hash értéket az elektronikus számláról (pl. PDF fájlról) és ezt a hash értéket adja át számla beküldéskor. Használandó hash algoritmus: SHA3-512.

Példa:

```php
$invoices = new NavOnlineInvoice\InvoiceOperations();

$pdfFileContent = "...";
$electronicInvoiceHash = NavOnlineInvoice\Util::sha3_512($pdfFileContent);

$invoices->add($invoiceXmlObject, "CREATE", $electronicInvoiceHash);

$transactionId = $reporter->manageInvoice($invoices);
```

_Megjegyzés: itt a NAV API elfogadná az SHA-256 hash-t is, viszont a `nav-online-invoice` modulban fixen rögzítve van a SHA3-512-es cryptoType mező, így csak ez utóbbi használható._


## completenessIndicator=true esetén

Ebben az esetben a hash-t az adatszolgáltatásban használt XML-ről (base64 kódolás után) kell képezni. Mivel a base64-es változatot a `nav-online-invoice` modul képezi az XML-ből, így a modul a hash-t is elkészíti automatikus, ha `completenessIndicator=true` (ezt nem kell külön átadni, maga a modul ellenőrzi az átadott számla $xml-ben ezt a mezőt).

Az elkészült hash-t opcionálisan lekérhetjük.

Példa:

```php
$invoices = new NavOnlineInvoice\InvoiceOperations();

$invoices->add($invoiceXmlObject, "CREATE");

$electronicInvoiceHash = $invoices->getLastInvoiceHash();
var_dump($electronicInvoiceHash);

$transactionId = $reporter->manageInvoice($invoices);
```

Ha az átadott xml-ben a `completenessIndicator=true` és kézileg is átadnánk 3. paraméterben egy hash-t értéket, akkor a modul Exception-t fog dobni.


