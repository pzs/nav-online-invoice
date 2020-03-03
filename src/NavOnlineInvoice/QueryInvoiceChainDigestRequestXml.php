<?php

namespace NavOnlineInvoice;


class QueryInvoiceChainDigestRequestXml extends BaseRequestXml {

    protected $rootName = "QueryInvoiceChainDigestRequest";


    /**
     * QueryInvoiceChainDigestRequestXml constructor.
     * @param $config
     * @param $page
     */
    function __construct($config, $invoiceChainQuery, $page) {
        parent::__construct($config);

        $this->xml->addChild("page", $page);

        XmlUtil::addChildArray($this->xml, "invoiceChainQuery", $invoiceChainQuery);
    }

}
