<?php

namespace NavOnlineInvoice;


class XmlUtil {

    public static function addChildArray(\SimpleXMLElement $xmlNode, $name, $data) {
        $node = $xmlNode->addChild($name);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                self::addChildArray($node, $key, $value);
            } else {
                $node->addChild($key, $value);
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

        $cleanedXmlString = preg_replace('/(<\/|<)[a-z0-9]+:([a-z0-9]+[ =>])/i', '$1$2', $xmlString);

        $cleanedXmlNode = simplexml_load_string($cleanedXmlString);

        return $cleanedXmlNode;
    }

}
