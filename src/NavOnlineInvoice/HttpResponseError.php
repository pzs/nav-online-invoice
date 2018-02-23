<?php

namespace NavOnlineInvoice;
use Exception;


class HttpResponseError extends Exception {

    protected $result;
    protected $httpStatusCode;


    function __construct($result, $httpStatusCode) {
        $this->result = $result;
        $this->httpStatusCode = $httpStatusCode;

        $message = "$result (HTTP Status code: $httpStatusCode)";

        parent::__construct($message);
    }


    public function getResult() {
        return $this->result;
    }


    public function getHttpStatusCode() {
        return $this->httpStatusCode;
    }

}
