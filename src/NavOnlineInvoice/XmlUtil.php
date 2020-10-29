<?php

namespace NavOnlineInvoice;


class XmlUtil {

    public static function addChildArray(\SimpleXMLElement $xmlNode, $name, $data) {
        $node = $xmlNode->addChild($name);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                self::addChildArray($node, $key, $value);
            } else {
                // NOTE: addChild($key, $value) does not escape the "&" sign,
                // see: https://stackoverflow.com/questions/552957/rationale-behind-simplexmlelements-handling-of-text-values-in-addchild-and-adda
                // and: https://github.com/pzs/nav-online-invoice/issues/34
                $node->{$key} = $value;
            }
        }
    }


    /**
     * Remove namespaces from XML elements
     *
     * @param  \SimpleXMLElement $xmlNode
     * @return \SimpleXMLElement $xmlNode
     */
    public static function removeNamespaces(\SimpleXMLElement $xmlNode) {
        $xmlString = $xmlNode->asXML();

        $cleanedXmlString = self::removeNamespacesFromXmlString($xmlString);

        $cleanedXmlNode = simplexml_load_string($cleanedXmlString);

        return $cleanedXmlNode;
    }


    /**
     * Remove namespaces from XML string
     *
     * @param  string $xmlString
     * @return string $xmlString
     */
    public static function removeNamespacesFromXmlString($xmlString) {
        return preg_replace('/(<\/|<)[a-z0-9]+:([a-z0-9]+[ =>])/i', '$1$2', $xmlString);
    }

}
