<?php

namespace NavOnlineInvoice;


class QueryTransactionStatusRequestXml extends BaseRequestXml {

    function __construct($config, $transactionId, $returnOriginalRequest = false) {
        parent::__construct("QueryTransactionStatusRequest", $config);
        $this->xml->addChild("transactionId", $transactionId);

        if ($returnOriginalRequest) {
            $this->xml->addChild("returnOriginalRequest", $returnOriginalRequest);
        }
    }

}
