<?php

namespace NavOnlineInvoice;

use NavOnlineInvoice\Config;


class QueryTaxpayerRequestXml extends BaseRequestXml
{

    protected string $rootName = "QueryTaxpayerRequest";

    function __construct(Config $config, ?string $taxNumber)
    {
        parent::__construct($config);

        $this->xml->addChild("taxNumber", $taxNumber);
    }
}
