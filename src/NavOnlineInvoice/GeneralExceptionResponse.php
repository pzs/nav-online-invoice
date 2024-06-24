<?php

namespace NavOnlineInvoice;

class GeneralExceptionResponse extends BaseExceptionResponse
{

    /** @return array<mixed> */
    public function getResult(): array
    {
        return (array)$this->xml;
    }
}
