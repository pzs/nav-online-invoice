<?php

namespace NavOnlineInvoice;


class QueryTransactionListRequestXml extends BaseRequestXml {

    protected $rootName = "QueryTransactionListRequest";


    /**
     * QueryTransactionListRequestXml constructor.
     * @param $config
     * @param $insDate
     * @param $page
     */
    function __construct($config, $insDate, $page) {
        parent::__construct($config);

        $this->xml->addChild("page", $page);

        XmlUtil::addChildArray($this->xml, "insDate", $insDate);
    }

}
