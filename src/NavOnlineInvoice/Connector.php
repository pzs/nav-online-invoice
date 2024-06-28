<?php

namespace NavOnlineInvoice;

use RuntimeException;
use SimpleXMLElement;
use NavOnlineInvoice\Config;

class Connector
{
    private ?string $lastRequestUrl = null;
    private ?string $lastRequestHeader = null;
    private ?string $lastRequestBody = null;
    private ?string $lastResponseHeader = null;
    private ?string $lastResponseBody = null;
    private ?string $lastRequestId = null;
    private ?SimpleXMLElement $lastResponseXml = null;


    public function __construct(
        protected Config $config,
    )
    {
        $this->config = $config;
    }


    private function resetDebugInfo(): void
    {
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
     * @return array<string,mixed>
     */
    public function getLastRequestData()
    {
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


    public function getLastResponseXml(): ?SimpleXMLElement
    {
        return $this->lastResponseXml;
    }


    /**
     *
     * @param  string $url
     * @param  string|\SimpleXMLElement|BaseRequestXml $requestXml
     * @return \SimpleXMLElement
     * @throws \NavOnlineInvoice\CurlError
     * @throws \NavOnlineInvoice\HttpResponseError
     * @throws \NavOnlineInvoice\GeneralExceptionResponse
     * @throws \NavOnlineInvoice\GeneralErrorResponse
     */
    public function post(string $url, string|\SimpleXMLElement|BaseRequestXml $requestXml): string|\SimpleXMLElement
    {
        $this->resetDebugInfo();

        $url = $this->config->baseUrl . $url;
        $this->lastRequestUrl = $url;

        $xmlString = is_string($requestXml) ? $requestXml : $requestXml->asXML();
        if($xmlString === false) {
            $xmlString = null;
        }
        $this->lastRequestBody = $xmlString;

        $this->lastRequestId = $requestXml instanceof BaseRequestXml ? $requestXml->getRequestId() : null;

        if ($this->config->validateApiSchema) {
            Xsd::validate($xmlString, Config::getApiXsdFilename());
        }

        $ch = $this->getCurlHandle($url, $xmlString);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $info = curl_getinfo($ch);
        if(!is_string($response)){
            throw new CurlError($errno);
        }
        $header = substr($response, 0, $info["header_size"]);
        $result = substr($response, $info["header_size"]);

        $httpStatusCode = $info["http_code"];
        //ignoring this line because give error the request_header not exits
        $this->lastRequestHeader = $info["request_header"] ?? null; // @phpstan-ignore-line
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


    private function getCurlHandle(?string $url, mixed $requestBody): \CurlHandle
    {
        $ch = curl_init($url);
        if($ch == false) {
            throw new \InvalidArgumentException('Curl init failed');
        }

        $headers = array(
            "Content-Type: application/xml;charset=UTF-8",
            "Accept: application/xml",
        );

        $curl_version = curl_version();
        if ($curl_version === false) {
            throw new \InvalidArgumentException('Curl version is not available');
        }

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


    private function parseResponse(string $xmlString): ?SimpleXMLElement
    {
        if (substr($xmlString, 0, 5) !== "<?xml") {
            return null;
        }

        $xmlString = XmlUtil::removeNamespacesFromXmlString($xmlString);

        $xmlElement = simplexml_load_string($xmlString);
        if($xmlElement === false) {
            throw new RuntimeException('Cant parse response');
        }

        return $xmlElement;
    }
}
