<?php

include("config.php");


$xml = simplexml_load_file(TEST_DATA_DIR . "invoice1.xml");
// $xml = simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml");

$errorMsg = NavOnlineInvoice\InvoiceOperations::getValidationError($xml);

if ($errorMsg) {
    print "A számla nem valid, hibaüzenet: " . $errorMsg;
} else {
    print "A számla valid.";
}

