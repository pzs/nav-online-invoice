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

        $this->addQueryData($this->xml, "invoiceNumberQuery", $invoiceNumberQuery);
    }


    protected function addQueryData(\SimpleXMLElement $xmlNode, $type, $data) {
        $node = $xmlNode->addChild($type);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->addQueryData($node, $key, $value);
            } else {
                $node->addChild($key, $value);
            }
        }
    }

}
