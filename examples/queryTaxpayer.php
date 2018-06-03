<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $result = $reporter->queryTaxpayer("12345678");

    if ($result) {
        print "Az adószám valid.\n";
        print "Az adószámhoz tartozó név: " . $result->taxpayerName . "\n";
        if (isset($result->taxpayerAddress)) {
            print "Cím: ";
            print_r($result->taxpayerAddress);
        } else {
            print "Az adószámhoz nem tartozik cím.";
        }
    } else {
        print "Az adószám nem valid.";
    }

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
