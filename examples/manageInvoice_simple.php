<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $invoiceXml = simplexml_load_file(TEST_DATA_DIR . "invoice1.xml");

    $transactionId = $reporter->manageInvoice($invoiceXml, "CREATE");

    print "Tranzakciós azonosító a státusz lekérdezéshez: " . $transactionId;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
