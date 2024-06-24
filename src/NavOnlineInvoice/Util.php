<?php

namespace NavOnlineInvoice;

use Exception;


class Util
{
    public static function isHashAlgoSupported(mixed $algo): bool
    {
        $algos = hash_algos();

        return in_array($algo, $algos);
    }

    public static function sha3_512(string $string): string
    {

        // Built-in SHA3-512
        if (self::isHashAlgoSupported("sha3-512")) {
            return strtoupper(hash("sha3-512", $string));
        }

        throw new Exception("SHA3-512 nem támogatott! Kérlek,\n- frissíts PHP 8.2.0 vagy e feletti verzióra;\n- vagy állíts be egy egyedi SHA3-512 függvényt;\n- vagy hivatkozd be az n-other/php-sha3 vagy desktopd/SHA könyvtárat.\nRészletekért lásd a nav-online-invoice README-t.");
    }


    public static function sha512(string $string): string
    {
        return strtoupper(hash("sha512", $string));
    }


    public static function aes128_decrypt(string $string, string $key): string|false
    {
        return openssl_decrypt($string, "AES-128-ECB", $key);
    }
}
