<?php

namespace NavOnlineInvoice;

use Exception;


class HttpResponseError extends Exception
{

    protected string $result;
    protected mixed $httpStatusCode;

    function __construct(string $result, mixed $httpStatusCode)
    {
        $this->result = $result;
        $this->httpStatusCode = $httpStatusCode;

        $message = "$result (HTTP Status code: $httpStatusCode)";

        parent::__construct($message);
    }


    public function getResult(): string
    {
        return $this->result;
    }


    public function getHttpStatusCode(): mixed
    {
        return $this->httpStatusCode;
    }
}
