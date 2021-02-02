<?php


class XmlUtilTest extends PHPUnit_Framework_TestCase {

    public function testAddChildArray1() {
        $xml = new SimpleXMLElement("<root/>");
        $name = "invoiceQueryParams";

        $invoiceQueryParams = [
            "mandatoryQueryParams" => [
                "invoiceIssueDate" => [
                    "dateFrom" => "2021-01-01",
                    "dateTo" => "2021-01-11",
                ],
            ],
        ];

        NavOnlineInvoice\XmlUtil::addChildArray($xml, $name, $invoiceQueryParams);

        $this->assertEquals($xml->invoiceQueryParams->asXML(), "<invoiceQueryParams><mandatoryQueryParams><invoiceIssueDate><dateFrom>2021-01-01</dateFrom><dateTo>2021-01-11</dateTo></invoiceIssueDate></mandatoryQueryParams></invoiceQueryParams>");
    }


    public function testAddChildArray2() {
        $xml = new SimpleXMLElement("<root/>");
        $name = "test";
        $data = ["2021-01-01", "2021-01-02", "2021-01-03"];

        NavOnlineInvoice\XmlUtil::addChildArray($xml, $name, $data);

        $this->assertEquals($xml->asXML(), "<?xml version=\"1.0\"?>\n<root><test>2021-01-01</test><test>2021-01-02</test><test>2021-01-03</test></root>\n");
    }


    public function testAddChildArray3() {
        $xml = new SimpleXMLElement("<root/>");
        $name = "invoiceQueryParams";

        $invoiceQueryParams = [
            "mandatoryQueryParams" => [
                "invoiceIssueDate" => [
                    "dateFrom" => "2021-01-01",
                    "dateTo" => "2021-01-30",
                ],
            ],
            "relationalQueryParams" => [
                "invoiceDelivery" => [
                    [
                        "queryOperator" => "GTE",
                        "queryValue" => "2021-01-01",
                    ],
                    [
                        "queryOperator" => "LTE",
                        "queryValue" => "2021-01-28",
                    ],
                ],
            ],
        ];

        NavOnlineInvoice\XmlUtil::addChildArray($xml, $name, $invoiceQueryParams);

        $this->assertEquals($xml->invoiceQueryParams->asXML(), "<invoiceQueryParams><mandatoryQueryParams><invoiceIssueDate><dateFrom>2021-01-01</dateFrom><dateTo>2021-01-30</dateTo></invoiceIssueDate></mandatoryQueryParams><relationalQueryParams><invoiceDelivery><queryOperator>GTE</queryOperator><queryValue>2021-01-01</queryValue></invoiceDelivery><invoiceDelivery><queryOperator>LTE</queryOperator><queryValue>2021-01-28</queryValue></invoiceDelivery></relationalQueryParams></invoiceQueryParams>");
    }

}
