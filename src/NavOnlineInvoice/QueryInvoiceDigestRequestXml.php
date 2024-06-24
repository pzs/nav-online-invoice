<?php

namespace NavOnlineInvoice;

use Exception;
use NavOnlineInvoice\Config;


class QueryInvoiceDigestRequestXml extends BaseRequestXml
{

    protected string $rootName = "QueryInvoiceDigestRequest";

    /**
     * QueryInvoiceDigestRequestXml constructor.
     * @param array<mixed> $invoiceQueryParams
     * @throws \Exception
     */
    function __construct(Config $config, array $invoiceQueryParams, ?string $page, ?string $direction)
    {
        parent::__construct($config);

        $this->xml->addChild("page", $page);
        $this->xml->addChild("invoiceDirection", $direction);

        XmlUtil::addChildArray($this->xml, "invoiceQueryParams", $invoiceQueryParams);
    }
}
