<?php

header("Content-Type: text/html; charset=utf-8");

define("TEST_DATA_DIR", __DIR__ . "/../tests/testdata/");

include("../autoload.php");

// NOTE: below PHP 7.1.0 download and include one of the SHA3 library:
// include_once(__DIR__ . "/../sha3-lib/bbSha3.php");
// include_once(__DIR__ . "/../sha3-lib/desktopdSHA3.php");

$apiUrl = NavOnlineInvoice\Config::TEST_URL; // https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3

$userData = array(
    "login" => "username",
    "password" => "password",
    // "passwordHash" => "...", // Opcionálisan, jelszó helyett a jelszó hash is átadható
    "taxNumber" => "12345678",
    "signKey" => "sign-key",
    "exchangeKey" => "exchange-key",
);

$softwareData = array(
    "softwareId" => "123456789123456789",
    "softwareName" => "string",
    "softwareOperation" => "ONLINE_SERVICE",
    "softwareMainVersion" => "string",
    "softwareDevName" => "string",
    "softwareDevContact" => "string",
    "softwareDevCountryCode" => "HU",
    "softwareDevTaxNumber" => "string",
);
