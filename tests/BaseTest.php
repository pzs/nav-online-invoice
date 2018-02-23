<?php


class BaseTest extends PHPUnit_Framework_TestCase {

    private $config;

    public function getConfig() {
        if (!$this->config) {
            $this->config = $this->createConfig();
        }

        return $this->config;
    }


    private function createConfig() {
        $apiUrl = "https://api-test.onlineszamla.nav.gov.hu/invoiceService";
        return new NavOnlineInvoice\Config($apiUrl, TEST_DATA_DIR . "userData.sample.json", TEST_DATA_DIR . "softwareData.json");
    }

}
