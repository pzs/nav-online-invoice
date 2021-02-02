<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $invoiceQueryParams = [
        "mandatoryQueryParams" => [
            "invoiceIssueDate" => [
                "dateFrom" => "2021-01-01",
                "dateTo" => "2021-01-11",
            ],
        ],
        "relationalQueryParams" => [
            "invoiceDelivery" => [
                "queryOperator" => "GTE",
                "queryValue" => "2021-01-01",
            ],
            // Több feltétel esetén ugyanazon elemhez tömbben adjuk át a gyerek elemeket
            "paymentDate" => [
                [
                    "queryOperator" => "GTE",
                    "queryValue" => "2021-01-01",
                ],
                [
                    "queryOperator" => "LTE",
                    "queryValue" => "2021-01-28",
                ],
            ],
        ],
    ];

    $page = 1;

    $invoiceDigestResult = $reporter->queryInvoiceDigest($invoiceQueryParams, $page, "OUTBOUND");

    print "Query results XML elem:\n";
    var_dump($invoiceDigestResult);

    // Request XML for debugging:
    // $data = $reporter->getLastRequestData();
    // $requestXml = $data['requestBody'];
    // var_dump($requestXml);

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
