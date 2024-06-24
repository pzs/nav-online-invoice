<?php

namespace NavOnlineInvoice;

use NavOnlineInvoice\Config;


class QueryTransactionListRequestXml extends BaseRequestXml {

    protected string $rootName = "QueryTransactionListRequest";

    /**
     * @param Config $config
     * @param array<mixed> $insDate
     * @param string|null $page
     */
    public function __construct(Config $config, array $insDate, ?string $page) {
        parent::__construct($config);

        $this->xml->addChild("page", $page);

        XmlUtil::addChildArray($this->xml, "insDate", $insDate);
    }

}
