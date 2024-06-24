<?php

namespace NavOnlineInvoice;

use NavOnlineInvoice\Config;


class QueryInvoiceChainDigestRequestXml extends BaseRequestXml {

    protected string $rootName = "QueryInvoiceChainDigestRequest";

    /**
     * @param Config $config
     * @param array<mixed> $invoiceChainQuery
     * @param string|null $page
     */
    public function __construct(Config $config, array $invoiceChainQuery, ?string $page) {
        parent::__construct($config);

        $this->xml->addChild("page", $page);

        XmlUtil::addChildArray($this->xml, "invoiceChainQuery", $invoiceChainQuery);
    }

}
