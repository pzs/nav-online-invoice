<?php

namespace NavOnlineInvoice;

class GeneralExceptionResponse extends BaseExceptionResponse {

    public function getResult() {
        return (array)$this->xml;
    }

}
