<?php

namespace NavOnlineInvoice;


class XmlUtil {

    /**
     * Add elements from array to XML node
     *
     * @param \SimpleXMLElement $xmlNode
     * @param string            $name
     * @param array             $data
     */
    public static function addChildArray(\SimpleXMLElement $xmlNode, $name, $data) {
        $isSeqArray = self::isSequentialArray($data);
        $node = $isSeqArray ? $xmlNode : $xmlNode->addChild($name);

        foreach ($data as $key => $value) {

            $childName = $isSeqArray ? $name : $key;

            if (is_array($value)) {
                self::addChildArray($node, $childName, $value);
            } else {
                self::addEscapedChild($node, $childName, $value);
            }
        }
    }


    /**
     * Gyerek elem hozzáadása escape-elt szöveges értékkel.
     *
     * A SimpleXMLElement::addChild($name, $value) a $value-ban nem escape-eli az "&" jelet,
     * hanem entitás kezdetnek tekinti, így pl. "Kovács & Társa" esetén üres elem jön létre.
     * Lásd:
     *  - https://github.com/pzs/nav-online-invoice/issues/34
     *  - https://stackoverflow.com/questions/552957/rationale-behind-simplexmlelements-handling-of-text-values-in-addchild-and-adda
     *
     * @param  \SimpleXMLElement $parent
     * @param  string            $name
     * @param  string            $value
     * @return \SimpleXMLElement A létrehozott elem
     */
    public static function addEscapedChild(\SimpleXMLElement $xmlNode, $childName, $value) {
        $child = $xmlNode->addChild($childName);
        $child[0] = $value;
        return $child;
    }


    /**
     * Returns true, if it's a sequantial array (keys are numeric)
     *
     * Source: https://stackoverflow.com/a/173479
     *
     * @param  array   $arr
     * @return boolean
     */
    private static function isSequentialArray(array $arr) {
        if (array() === $arr) {
            return true;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
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
