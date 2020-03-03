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

}
