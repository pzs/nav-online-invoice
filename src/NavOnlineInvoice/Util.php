<?php

namespace NavOnlineInvoice;
use Exception;


class Util {

    public static $customSha3_512Function = null;


    public static function isHashAlgoSupported($algo) {
        $algos = hash_algos();

        return in_array($algo, $algos);
    }


    private static function desktopdSHA3_512($string) {
        $sponge = \desktopd\SHA3\Sponge::init(\desktopd\SHA3\Sponge::SHA3_512);
        $sponge->absorb($string);

        return strtoupper(bin2hex($sponge->squeeze()));
    }


    public static function sha3_512($string) {

        // Built-in SHA3-512 from PHP 7.1.0
        if (self::isHashAlgoSupported("sha3-512")) {
            return strtoupper(hash("sha3-512", $string));
        }

        // User provided function
        if (self::$customSha3_512Function and is_callable(self::$customSha3_512Function)) {
            return call_user_func(self::$customSha3_512Function, $string);
        }

        // https://packagist.org/packages/n-other/php-sha3
        if (class_exists("\\bb\Sha3\\Sha3")) {
            return strtoupper(\bb\Sha3\Sha3::hash($string, 512));
        }

        // Desktopd SHA3 (https://notabug.org/desktopd/PHP-SHA3-Streamable)
        if (class_exists("\\desktopd\\SHA3\\Sponge")) {
            return self::desktopdSHA3_512($string);
        }

        throw new Exception("SHA3-512 nem támogatott! Kérlek,\n- frissíts PHP 7.1.0 vagy e feletti verzióra;\n- vagy állíts be egy egyedi SHA3-512 függvényt;\n- vagy hivatkozd be az n-other/php-sha3 vagy desktopd/SHA könyvtárat.\nRészletekért lásd a nav-online-invoice README-t.");
    }


    public static function sha512($string) {
        return strtoupper(hash("sha512", $string));
    }


    public static function aes128_decrypt($string, $key) {
        return openssl_decrypt($string, "AES-128-ECB", $key);
    }

}
