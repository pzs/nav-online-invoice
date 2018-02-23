<?php

namespace NavOnlineInvoice;
use Exception;


class CurlError extends Exception {

    protected $errno;


    function __construct($errno) {
        $this->errno = $errno;

        $message = "Connection error. CURL error code: $errno";

        parent::__construct($message);
    }


    public function getErrno() {
        return $this->errno;
    }

}
