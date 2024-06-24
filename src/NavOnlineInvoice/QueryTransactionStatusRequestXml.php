<?php

namespace NavOnlineInvoice;


class QueryTransactionStatusRequestXml extends BaseRequestXml {

    protected string $rootName = "QueryTransactionStatusRequest";

    function __construct(Config $config, ?string $transactionId, ?string $returnOriginalRequest = null) {
        parent::__construct($config);

        $this->xml->addChild("transactionId", $transactionId);

        if ($returnOriginalRequest) {
            $this->xml->addChild("returnOriginalRequest", $returnOriginalRequest);
        }
    }

}
