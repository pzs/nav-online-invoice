<?php

namespace NavOnlineInvoice;
use Exception;


class QueryInvoiceDigestRequestXml extends BaseRequestXml {

    protected $rootName = "QueryInvoiceDigestRequest";


    /**
     * QueryInvoiceDigestRequestXml constructor.
     * @param $config
     * @param $invoiceQueryParams
     * @param $page
     * @param $direction
     * @throws \Exception
     */
    function __construct($config, $invoiceQueryParams, $page, $direction) {
        parent::__construct($config);

        $this->xml->addChild("page", $page);
        $this->xml->addChild("invoiceDirection", $direction);

        XmlUtil::addChildArray($this->xml, "invoiceQueryParams", $invoiceQueryParams);
    }

}
