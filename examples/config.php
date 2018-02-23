<?php

header("Content-Type: text/html; charset=utf-8");

define("TEST_DATA_DIR", __DIR__ . "/../tests/testdata/");


$apiUrl = "https://api-test.onlineszamla.nav.gov.hu/invoiceService";

$userDataFilename = TEST_DATA_DIR . "userData.real.json";
$softwareDataFilename = TEST_DATA_DIR . "softwareData.json";


include("../autoload.php");
