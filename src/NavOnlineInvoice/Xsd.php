<?php

namespace NavOnlineInvoice;
use DOMDocument;


class Xsd {

    /**
     * A megadott XML-t (string) ellenőrzi a megadott XSD sémával.
     * Hiba esetén XsdValidationError exception-t dob.
     *
     * @param  string $xmlString
     * @param  string $xsdFilename
     * @throws XsdValidationError
     */
    public static function validate($xmlString, $xsdFilename) {
        $doc = new DOMDocument();
        $doc->loadXML($xmlString);

        $prevValue = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $isValid = $doc->schemaValidate($xsdFilename);

        if (!$isValid) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            libxml_use_internal_errors($prevValue);
            throw new XsdValidationError($errors);
        }

        libxml_use_internal_errors($prevValue);
    }

}
