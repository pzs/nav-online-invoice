<?php


if (!class_exists("PHPUnit_Framework_TestCase")) {
    class PHPUnit_Framework_TestCase extends PHPUnit\Framework\TestCase {}
}

define("TEST_DATA_DIR", __DIR__ . "/testdata/");

$sha3Lib = __DIR__ . "/../sha3-lib/bbSha3.php";

if (file_exists($sha3Lib)) {
    include_once($sha3Lib);
}

include_once(__DIR__ . "/../autoload.php");
include_once(__DIR__ . "/BaseTest.php");
