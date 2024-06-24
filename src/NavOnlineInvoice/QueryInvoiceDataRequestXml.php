<?php

namespace NavOnlineInvoice;

use Exception;
use NavOnlineInvoice\Config;


class QueryInvoiceDataRequestXml extends BaseRequestXml
{

    protected string $rootName = "QueryInvoiceDataRequest";

    /**
     * QueryInvoiceDataRequestXml constructor.
     * @param Config $config
     * @param array<mixed> $invoiceNumberQuery
     * @throws \Exception
     */
    function __construct($config, $invoiceNumberQuery)
    {
        parent::__construct($config);

        XmlUtil::addChildArray($this->xml, "invoiceNumberQuery", $invoiceNumberQuery);
    }
}
