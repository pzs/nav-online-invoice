<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $invoiceChainQuery = [
        "invoiceNumber" => "SZML-123",
        "invoiceDirection" => "OUTBOUND", // OUTBOUND or INBOUND
        "taxNumber" => "12345678", // optional
    ];
    $page = 1;

    $invoiceChainDigestResult = $reporter->queryInvoiceChainDigest($invoiceChainQuery, $page);

    print "Result:\n";
    print_r($invoiceChainDigestResult);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
