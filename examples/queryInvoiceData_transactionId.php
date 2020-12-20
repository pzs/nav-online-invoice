<?php

// Transaction ID keresése számla/számlaszám alapján queryInvoiceData() segítségével

include("config.php");

try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    // Keresett számla sorszáma
    $invoiceNumber = "ZZZ000001";

    $invoiceNumberQuery = [
        "invoiceNumber" => $invoiceNumber,
        "invoiceDirection" => "OUTBOUND",
    ];

    $foundInvoiceXml = $reporter->queryInvoiceData($invoiceNumberQuery, true);

    if (!$foundInvoiceXml) {
        print "Keresett számla nem található.";
        exit;
    }

    // TODO
    // $foundInvoiceXml és beküldendő számla XML összehasonlítása, hogy megegyezik-e

    // Transaction ID kiolvasása az XML válaszból
    $xml = $reporter->getLastResponseXml();
    $transactionId = (string)$xml->invoiceDataResult->auditData->transactionId;

    if (!$transactionId) {
        print "transactionId nem létezik, a számla nem gépi interface-en lett beküldve";
        exit;
    }

    print "transactionId: $transactionId";


} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
