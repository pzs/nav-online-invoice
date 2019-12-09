<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $annulmentXml = simplexml_load_file(TEST_DATA_DIR . "invoice_annul.xml");

    // Technikai érvénytelenítés
    $transactionId = $reporter->manageAnnulment($annulmentXml);

    print "Tranzakciós azonosító a státusz lekérdezéshez: " . $transactionId;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
