<?php

include("config.php");


$config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
$reporter = new NavOnlineInvoice\Reporter($config);

$invoiceXml = simplexml_load_file(TEST_DATA_DIR . "invoice1.xml");
// $invoiceXml = simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml");

$errorMsg = $reporter->getInvoiceValidationError($invoiceXml);

if ($errorMsg) {
    print "A számla nem valid, hibaüzenet: " . $errorMsg;
} else {
    print "A számla valid.";
}

