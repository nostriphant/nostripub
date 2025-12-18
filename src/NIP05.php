<?php

namespace nostriphant\nostripub;

final readonly class NIP05 {
    
    public static function lookup(string $nostr_user, string $nostr_domain) : array {
        error_log('Requesting https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user);
        $curl = curl_init('https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($info['http_code'] !== 200) {
            error_log('Received not OK');
            return [];
        }

        $json = json_decode($body, true);
        if (isset($json['names'][$nostr_user]) === false) {
            error_log($nostr_user . ' does not exists at ' . $nostr_domain);
            return [];
        }
        return $json;
    }
}
