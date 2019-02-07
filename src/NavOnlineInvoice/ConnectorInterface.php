<?php

namespace NavOnlineInvoice;

interface ConnectorInterface {

    /**
     *
     * @param  string                   $url
     * @param  string|\SimpleXMLElement $requestXml
     * @return \SimpleXMLElement
     */
    public function post($url, $requestXml);

}
