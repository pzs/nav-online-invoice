<?php

namespace NavOnlineInvoice;


class QueryTaxpayerRequestXml extends BaseRequestXml {

    function __construct($config, $taxNumber) {
        parent::__construct("QueryTaxpayerRequest", $config);
        $this->xml->addChild("taxNumber", $taxNumber);
    }

}
