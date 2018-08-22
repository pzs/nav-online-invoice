<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $invoiceXml = simplexml_load_file(TEST_DATA_DIR . "invoice_annul.xml");

    // Technikai érvénytelenítés
    $transactionId = $reporter->manageInvoice($invoiceXml, "ANNUL");

    print "Tranzakciós azonosító a státusz lekérdezéshez: " . $transactionId;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
