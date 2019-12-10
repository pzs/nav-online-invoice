<?php

namespace NavOnlineInvoice;


class QueryTransactionStatusRequestXml extends BaseRequestXml {

    protected $rootName = "QueryTransactionStatusRequest";

    function __construct($config, $transactionId, $returnOriginalRequest = false) {
        parent::__construct($config);

        $this->xml->addChild("transactionId", $transactionId);

        if ($returnOriginalRequest) {
            $this->xml->addChild("returnOriginalRequest", $returnOriginalRequest);
        }
    }

}
