<?php

namespace NavOnlineInvoice;
use Exception;


class GeneralErrorResponse extends Exception {

    protected $xml;


    function __construct($xml) {
        $this->xml = $xml;
        $result = $this->getResult();

        if (empty($result["message"])) {
            $message = $result["errorCode"];
        } elseif (empty($result["errorCode"])) {
            $message = $result["message"];
        } else {
            $message = "$result[message] ($result[errorCode])";
        }

        parent::__construct($message);
    }


    public function getXml() {
        return $this->xml;
    }


    public function getResult() {
        return (array)$this->xml->result;
    }


    public function getErrorCode() {
        return (string)$this->xml->result->errorCode;
    }

}
