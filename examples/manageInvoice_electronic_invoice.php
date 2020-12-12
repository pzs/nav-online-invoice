<?php

include("config.php");

// Példa elektronikus számlázásnál történő adatbeküldésre

try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $invoices = new NavOnlineInvoice\InvoiceOperations();

    // Számla, ahol completenessIndicator=true. Ilyenkor a nav-invoice-modul automatikusan számolja a hash-t,
    // így azt nem kell átadni a 3. paraméterben. A számolt hash hozzáadás után lekérhető.

    $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1_complete.xml"));

    $electronicInvoiceHash = $invoices->getLastInvoiceHash();
    var_dump($electronicInvoiceHash);


    // Számla, ahol completenessIndicator=false. Ilyenkor a hash-t a felhasználónak kell képezni pl. a PDF számláról
    // SHA3-512 algoritmussal és azt a 3. paraméterben át kell adni.

    $pdfFileContent = "...";

    $hash = NavOnlineInvoice\Util::sha3_512($pdfFileContent);
    $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice2.xml"), "CREATE", $hash);


    // Számlák beküldése:
    $transactionId = $reporter->manageInvoice($invoices);

    print "Tranzakciós azonosító a státusz lekérdezéshez: " . $transactionId;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
