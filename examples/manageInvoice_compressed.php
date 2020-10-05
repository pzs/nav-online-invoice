<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $compression = true;
    $invoices = new NavOnlineInvoice\InvoiceOperations($compression);

    // Maximum 100db számla küldhető be egyszerre
    $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1.xml"));
    // $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice2.xml"));

    // Számlák beküldése:
    $transactionId = $reporter->manageInvoice($invoices);

    print "Tranzakciós azonosító a státusz lekérdezéshez: " . $transactionId;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
