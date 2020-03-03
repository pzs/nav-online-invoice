<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $insDate = [
        "dateTimeFrom" => "2020-03-01T06:00:00Z",
        "dateTimeTo" => "2020-03-05T18:00:00Z",
    ];
    $page = 1;

    $transactionListResult = $reporter->queryTransactionList($insDate, $page);

    print "Result:\n";
    print_r($transactionListResult);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
