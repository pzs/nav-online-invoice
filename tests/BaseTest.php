<?php

use NavOnlineInvoice\Config;


class BaseTest extends PHPUnit_Framework_TestCase
{

    private ?Config $config = null;

    public function getConfig(): Config
    {
        if (!$this->config) {
            $this->config = $this->createConfig();
        }

        return $this->config;
    }


    private function createConfig(): Config
    {
        $apiUrl = "https://api-test.onlineszamla.nav.gov.hu/invoiceService";

        return new Config($apiUrl, TEST_DATA_DIR . "userData.sample.json", TEST_DATA_DIR . "softwareData.json");
    }
}
