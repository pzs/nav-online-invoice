<?php

namespace NavOnlineInvoice;

class GeneralErrorResponse extends BaseExceptionResponse
{

    /** @return array<mixed> */
    public function getResult(): array
    {
        return (array)$this->xml->result;
    }
}
