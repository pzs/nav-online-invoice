<?php

namespace NavOnlineInvoice;


class QueryTaxpayerRequestXml extends BaseRequestXml {

    protected $rootName = "QueryTaxpayerRequest";


    function __construct($config, $taxNumber) {
        parent::__construct($config);

        $this->xml->addChild("taxNumber", $taxNumber);
    }

}
