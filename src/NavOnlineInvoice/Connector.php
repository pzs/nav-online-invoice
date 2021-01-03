<?php

namespace NavOnlineInvoice;


class Connector {

    protected $config;

    private $lastRequestUrl = null;
    private $lastRequestHeader = null;
    private $lastRequestBody = null;
    private $lastResponseHeader = null;
    private $lastResponseBody = null;
    private $lastRequestId = null;
    private $lastResponseXml = null;


    /**
     *
     * @param Config  $config
     */
    function __construct($config) {
        $this->config = $config;
    }


    private function resetDebugInfo() {
        $this->lastRequestUrl = null;
        $this->lastRequestHeader = null;
        $this->lastRequestBody = null;
        $this->lastResponseHeader = null;
        $this->lastResponseBody = null;
        $this->lastRequestId = null;
        $this->lastResponseXml = null;
    }


    /**
     * Utolsó REST hívás adatainak lekérdezése
     *
     * @return array
     */
    public function getLastRequestData() {
        return array(
            'requestUrl' => $this->lastRequestUrl,
            'requestHeader' => $this->lastRequestHeader,
            'requestBody' => $this->lastRequestBody,
            'responseHeader' => $this->lastResponseHeader,
            'responseBody' => $this->lastResponseBody,
            'requestId' => $this->lastRequestId,
            'responseXml' => $this->lastResponseXml,
        );
    }


    public function getLastResponseXml() {
        return $this->lastResponseXml;
    }


    /**
     *
     * @param  string                   $url
     * @param  string|\SimpleXMLElement $requestXml
     * @return \SimpleXMLElement
     * @throws \NavOnlineInvoice\CurlError
     * @throws \NavOnlineInvoice\HttpResponseError
     * @throws \NavOnlineInvoice\GeneralExceptionResponse
     * @throws \NavOnlineInvoice\GeneralErrorResponse
     */
    public function post($url, $requestXml) {
        $this->resetDebugInfo();

        $url = $this->config->baseUrl . $url;
        $this->lastRequestUrl = $url;

        $xmlString = is_string($requestXml) ? $requestXml : $requestXml->asXML();
        $this->lastRequestBody = $xmlString;

        $this->lastRequestId = $requestXml instanceof BaseRequestXml ? $requestXml->getRequestId() : null;

        if ($this->config->validateApiSchema) {
            Xsd::validate($xmlString, Config::getApiXsdFilename());
        }

        $ch = $this->getCurlHandle($url, $xmlString);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $info = curl_getinfo($ch);
        $header = substr($response, 0, $info["header_size"]);
        $result = substr($response, $info["header_size"]);

        $httpStatusCode = $info["http_code"];

        $this->lastRequestHeader = isset($info["request_header"]) ? $info["request_header"] : null;
        $this->lastResponseHeader = $header;
        $this->lastResponseBody = $result;

        curl_close($ch);

        if ($errno) {
            throw new CurlError($errno);
        }

        $responseXml = $this->parseResponse($result);

        $this->lastResponseXml = $responseXml;

        if (!$responseXml) {
            throw new HttpResponseError($result, $httpStatusCode);
        }

        if ($responseXml->getName() === "GeneralExceptionResponse") {
            throw new GeneralExceptionResponse($responseXml);
        }

        if ($responseXml->getName() === "GeneralErrorResponse") {
            throw new GeneralErrorResponse($responseXml);
        }

        // TODO: felülvizsgálni, hogy ez minden esetben jó megoldás-e itt, illetve esetleg más típusú Exception dobása
        // Ha a result->funcCode !== OK értékkel, akkor Exception dobása
        if ((string)$responseXml->result->funcCode !== "OK") {
            throw new GeneralErrorResponse($responseXml);
        }

        // Fejlesztés idő alatt előfordult, hogy funcCode === OK, de a service nem megy
        if (!empty($responseXml->result->message) and preg_match("/endpoint is currently down/", $responseXml->result->message)) {
            throw new GeneralErrorResponse($responseXml);
        }

        return $responseXml;
    }


    private function getCurlHandle($url, $requestBody) {
        $ch = curl_init($url);

        $headers = array(
            "Content-Type: application/xml;charset=UTF-8",
            "Accept: application/xml",
        );

        $curl_version = curl_version();

        if (version_compare($curl_version['version'], '7.69') < 0) {
            $headers[] = "Expect:";
            //ha eredeti értékét megtartjuk, akkor NAV üres body-t add vissza nagy méretű válaszok esetén
            //@see https://daniel.haxx.se/blog/2020/02/27/expect-tweaks-in-curl/
            //(cURL <7.69)
        }

        if (!$this->config->verifySSL) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        if ($this->config->curlTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->config->curlTimeout);
        }

        return $ch;
    }


    private function parseResponse($xmlString) {
        if (substr($xmlString, 0, 5) !== "<?xml") {
            return null;
        }

        $xmlString = XmlUtil::removeNamespacesFromXmlString($xmlString);

        return simplexml_load_string($xmlString);
    }

}
