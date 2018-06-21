<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $queryData = [
        "invoiceIssueDateFrom" => "2018-02-01",
        "invoiceIssueDateTo" => "2018-02-01",
        "customerTaxNumber" => "30073246",
        // ...
        "transactionParams" => [
            "transactionId" => "string"
        ]
    ];
    $queryResults = $reporter->queryInvoiceData("queryParams", $queryData);

    print "Query results XML elem:\n";
    var_dump($queryResults);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
