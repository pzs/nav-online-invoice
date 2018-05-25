<?php

namespace NavOnlineInvoice;
use Exception;


class GeneralExceptionResponse extends BaseExceptionResponse {

    public function getResult() {
        return (array)$this->xml;
    }

}
