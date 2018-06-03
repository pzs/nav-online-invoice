<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $invoices = new NavOnlineInvoice\InvoiceOperations();

    $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice1.xml"));
    $invoices->add(simplexml_load_file(TEST_DATA_DIR . "invoice2.xml"));

    $transactionId = $reporter->manageInvoice($invoices);

    print "Tranzakciós azonosító a státusz lekérdezéshez: " . $transactionId;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
