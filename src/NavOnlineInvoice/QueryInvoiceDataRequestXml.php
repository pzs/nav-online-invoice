<?php

namespace NavOnlineInvoice;
use Exception;


class QueryInvoiceDataRequestXml extends BaseRequestXml {

    private static $queryTypes = ["invoiceQuery", "queryParams"];


    function __construct($config, $queryType, $queryData) {
        if (!in_array($queryType, self::$queryTypes)) {
            throw new Exception("Érvénytelen queryType: $queryType");
        }

        parent::__construct("QueryInvoiceDataRequest", $config);

        $this->addQueryData($this->xml, $queryType, $queryData);
    }


    protected function addQueryData($xmlNode, $type, $data) {
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
