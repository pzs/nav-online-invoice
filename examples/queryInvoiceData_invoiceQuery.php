<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
    $reporter = new NavOnlineInvoice\Reporter($config);

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
