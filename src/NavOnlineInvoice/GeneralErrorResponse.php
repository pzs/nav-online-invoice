<?php

namespace NavOnlineInvoice;
use Exception;


class GeneralErrorResponse extends BaseExceptionResponse {

    public function getResult() {
        return (array)$this->xml->result;
    }

}
