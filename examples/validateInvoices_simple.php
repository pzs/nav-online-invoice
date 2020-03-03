<?php

include("config.php");


$invoiceXml = simplexml_load_file(TEST_DATA_DIR . "invoice1.xml");
// $invoiceXml = simplexml_load_file(TEST_DATA_DIR . "invoice1_invalid.xml");

$errorMsg = NavOnlineInvoice\Reporter::getInvoiceValidationError($invoiceXml);

if ($errorMsg) {
    print "A számla nem valid, hibaüzenet: " . $errorMsg;
} else {
    print "A számla valid.";
}
