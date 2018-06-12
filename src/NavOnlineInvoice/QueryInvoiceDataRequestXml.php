<?php

namespace NavOnlineInvoice;
use Exception;


class QueryInvoiceDataRequestXml extends BaseRequestXml {

    private static $queryTypes = array("invoiceQuery", "queryParams");


    /**
     * QueryInvoiceDataRequestXml constructor.
     * @param $config
     * @param $queryType
     * @param $queryData
     * @param $page
     * @throws \Exception
     */
    function __construct($config, $queryType, $queryData, $page) {
        if (!in_array($queryType, self::$queryTypes)) {
            throw new Exception("Érvénytelen queryType: $queryType");
        }

        if (!is_int($page) or $page < 1) {
            throw new Exception("Érvénytelen oldalszám: " . $page);
        }

        parent::__construct("QueryInvoiceDataRequest", $config);

        $this->xml->addChild("page", $page);
        $this->addQueryData($this->xml, $queryType, $queryData);
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
