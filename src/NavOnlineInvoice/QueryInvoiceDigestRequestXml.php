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

        $this->addQueryData($this->xml, "invoiceQueryParams", $invoiceQueryParams);
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
