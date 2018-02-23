<?php

namespace NavOnlineInvoice;


class Util {

    public static function crc32($string) {
        return sprintf("%u", crc32($string));
    }


    public static function sha512($string) {
        return strtoupper(hash("sha512", $string));
    }


    public static function aes128_decrypt($string, $key) {
        return openssl_decrypt($string, "AES-128-ECB", $key);
    }

}
