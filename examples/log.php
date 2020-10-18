<?php

include("config.php");

$config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
$reporter = new NavOnlineInvoice\Reporter($config);


try {
    $invoiceXml = simplexml_load_file(TEST_DATA_DIR . "invoice1.xml");

    $transactionId = $reporter->manageInvoice($invoiceXml, "CREATE");

    print "Tranzakciós azonosító a státusz lekérdezéshez: " . $transactionId;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}

$data = $reporter->getLastRequestData();

print "<br /><br /><b>Request URL:</b> " . htmlspecialchars($data['requestUrl']);
print "<br /><br /><b>Request body:</b> " . htmlspecialchars($data['requestBody']);
print "<br /><br /><b>Response body:</b> " . htmlspecialchars($data['responseBody']);
print "<br /><br /><b>Request ID:</b> " . htmlspecialchars($data['requestId']);
