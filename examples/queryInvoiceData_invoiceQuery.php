<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
    $config->useApiSchemaValidation();
    $reporter = new NavOnlineInvoice\Reporter($config);

    $queryData = [
        "invoiceNumber" => "T20190001",
        "requestAllModification" => true
    ];
    $responseXml = $reporter->queryInvoiceData("invoiceQuery", $queryData);

    print "Válasz XML objektum:\n";
    var_dump($responseXml);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
