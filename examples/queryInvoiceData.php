<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

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
