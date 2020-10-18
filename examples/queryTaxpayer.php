<?php

include("config.php");


try {
    $config = new NavOnlineInvoice\Config($apiUrl, $userData, $softwareData);
    $reporter = new NavOnlineInvoice\Reporter($config);

    $result = $reporter->queryTaxpayer("12345678");

    if ($result) {
        print "Az adószám valid.\n";
        print "Az adószámhoz tartozó név: $result->taxpayerName\n";

        print "További lehetséges információk az adózóról:\n";
        print_r($result->taxpayerShortName);
        print_r($result->taxNumberDetail);
        print_r($result->vatGroupMembership);
        print_r($result->taxpayerAddressList);

    } else {
        print "Az adószám nem valid.";
    }

    // infoDate
    // A queryTaxpayer válaszában szerepel még egy infoDate mező, viszont ez a
    // taxpayerData mellett van, ennek kiolvasását a teljes XML-ből tudjuk megtenni:

    $responseXml = $reporter->getLastResponseXml();

    print $responseXml->infoDate;


} catch(Exception $ex) {
    print get_class($ex) . ": " . $ex->getMessage();
}
