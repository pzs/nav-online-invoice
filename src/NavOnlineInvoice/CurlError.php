<?php

namespace NavOnlineInvoice;

use Exception;


class CurlError extends Exception
{

    function __construct(protected int $errno)
    {
        $message = "Connection error. CURL error code: $errno";
        parent::__construct($message);
    }


    public function getErrno(): int
    {
        return $this->errno;
    }
}
