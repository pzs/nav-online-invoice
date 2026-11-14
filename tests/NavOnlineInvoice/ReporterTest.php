<?php


class TestReporter extends NavOnlineInvoice\Reporter {

    public function publicDecodeToken($encodedToken) {
        return $this->decodeToken($encodedToken);
    }

}


class ReporterTest extends BaseTest {

    private function createReporter() {
        return new TestReporter($this->getConfig());
    }


    private function encodeToken($token, $key) {
        return openssl_encrypt($token, "AES-128-ECB", $key);
    }


    public function testDecodeToken() {
        $config = $this->getConfig();
        $token = "a1b2c3d4-e5f6-7890-abcd-ef1234567890";
        $encodedToken = $this->encodeToken($token, $config->user["exchangeKey"]);

        $this->assertSame($token, $this->createReporter()->publicDecodeToken($encodedToken));
    }


    public function testDecodeTokenWithWrongKey() {
        // Ezzel a kulccsal a dekódolás biztosan sikertelen (hibás padding), így determinisztikus a teszt
        $encodedToken = "16x4YSni64DR+FmUGMckH3bKe9bVSJLz/XppQdRUKqiKNnY2hxCFu7O002A8uOD3";

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("exchangeKey");
        $this->createReporter()->publicDecodeToken($encodedToken);
    }


    public function testDecodeEmptyToken() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("exchangeKey");
        $this->createReporter()->publicDecodeToken("");
    }

}
