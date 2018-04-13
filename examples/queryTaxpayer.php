<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
    $config->useApiSchemaValidation();
    $reporter = new NavOnlineInvoice\Reporter($config);

    $result = $reporter->queryTaxpayer("12345678");

    if (!$result) {
        print "Az adószám nem valid.";
    } else {
        print "Az adószám valid.\n";
        print "Az adószámhoz tartozó név és címadatok: " . $result->taxpayerName . "\n";
        print_r($result->taxpayerAddress);
    }

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
