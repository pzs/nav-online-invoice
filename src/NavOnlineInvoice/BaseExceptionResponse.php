<?php

namespace NavOnlineInvoice;
use Exception;


abstract class BaseExceptionResponse extends Exception {

    protected $xml;


    function __construct($xml) {
        $this->xml = $xml;
        $message = $this->getResultMessage();

        parent::__construct($message);
    }


    public function getXml() {
        return $this->xml;
    }


    /**
     * Return the result field of the XML in array format
     * @return array
     */
    abstract public function getResult();


    public function getResultMessage() {
        $result = $this->getResult();

        if (empty($result["message"]) and empty($result["errorCode"])) {
            $message = "";
        }elseif (empty($result["message"])) {
            $message = $result["errorCode"];
        } elseif (empty($result["errorCode"])) {
            $message = $result["message"];
        } else {
            $message = "$result[message] ($result[errorCode])";
        }

        return $message;
    }


    public function getErrorCode() {
        $result = $this->getResult();

        if (isset($result["errorCode"])) {
            return (string)$result["errorCode"];
        }

        return null;
    }

}
