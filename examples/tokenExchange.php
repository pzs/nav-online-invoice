<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userDataFilename);
    $config->useApiSchemaValidation();
    $reporter = new NavOnlineInvoice\Reporter($config);

    $token = $reporter->tokenExchange();
    print "Token: " . $token;

} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
