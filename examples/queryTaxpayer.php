<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
    $config->useApiSchemaValidation();
    $reporter = new NavOnlineInvoice\Reporter($config);

    $isValid = $reporter->queryTaxpayer("12345678");
    print "Az adószám: " . ($isValid ? "valid" : "nem valid");

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
