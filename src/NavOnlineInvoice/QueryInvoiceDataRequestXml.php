<?php

namespace NavOnlineInvoice;
use Exception;


class QueryInvoiceDataRequestXml extends BaseRequestXml {

    protected $rootName = "QueryInvoiceDataRequest";


    /**
     * QueryInvoiceDataRequestXml constructor.
     * @param $config
     * @param $invoiceNumberQuery
     * @throws \Exception
     */
    function __construct($config, $invoiceNumberQuery) {
        parent::__construct($config);

        XmlUtil::addChildArray($this->xml, "invoiceNumberQuery", $invoiceNumberQuery);
    }

}
