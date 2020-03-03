<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $invoiceNumberQuery = [
        "invoiceNumber" => "T20190001",
        "invoiceDirection" => "OUTBOUND",
    ];
    $invoiceDataResult = $reporter->queryInvoiceData($invoiceNumberQuery);

    print "Query results XML elem:\n";
    var_dump($invoiceDataResult);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
