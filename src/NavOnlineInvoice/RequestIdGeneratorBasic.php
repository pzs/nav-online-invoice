<?php

namespace NavOnlineInvoice;

class RequestIdGeneratorBasic implements RequestIdGeneratorInterface {

    /**
     * Egyedi request ID generálása
     *
     * NAV specifikáció:
     * A requestId a kérés azonosítója. Értéke bármi lehet, ami a pattern szerint érvényes és az
     * egyediséget nem sérti. A requestId-nak - az adott adózó vonatkozásában - kérésenként
     * egyedinek kell lennie. Az egyediségbe csak a sikeresen feldolgozott kérések számítanak bele, a
     * sikertelen vagy a szerver által elutasított kérések azonosítói nem, azok az első sikeres
     * tranzakcióig (HTTP 200-as válaszig) újra felhasználhatóak. A tag értéke beleszámít a
     * requestSignature értékébe.
     *
     * Pattern: [+a-zA-Z0-9_]{1,30}
     *
     * @return string
     */
    function generate() {
        $id = "RID" . microtime() . mt_rand(10000, 99999);
        $id = preg_replace("/[^A-Z0-9]/", "", $id);
        $id = substr($id, 0, 30);
        return $id;
    }

}
