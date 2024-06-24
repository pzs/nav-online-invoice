<?php

namespace NavOnlineInvoice;

use Exception;
use SimpleXMLElement;


abstract class BaseExceptionResponse extends Exception
{
    protected SimpleXMLElement $xml;

    function __construct(SimpleXMLElement $xml)
    {
        $this->xml = $xml;
        $message = $this->getResultMessage();

        parent::__construct($message);
    }

    public function getXml(): SimpleXMLElement
    {
        return $this->xml;
    }


    /**
     * Return the result field of the XML in array format
     * @return array<mixed>
     */
    abstract public function getResult();

    /**
     * @return string
     */
    public function getResultMessage()
    {
        $result = $this->getResult();

        if (empty($result["message"])) {
            $message = $result["errorCode"];
        } elseif (empty($result["errorCode"])) {
            $message = $result["message"];
        } else {
            $message = "$result[message] ($result[errorCode])";
        }

        return $message;
    }


    public function getErrorCode(): ?string
    {
        $result = $this->getResult();

        if (isset($result["errorCode"])) {
            return (string)$result["errorCode"];
        }

        return null;
    }
}
