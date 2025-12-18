<?php

namespace nostriphant\nostripub;

final readonly class NIP05 {
    
    public function __construct(public ?string $pubkey, public array $relays) {
        
    }
    
    public static function lookup(string $nostr_user, string $nostr_domain) : self {
        error_log('Requesting https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user);
        $curl = curl_init('https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($info['http_code'] !== 200) {
            error_log('Received not OK');
            return new self(null, []);
        }

        $json = json_decode($body, true);
        if (isset($json['names'][$nostr_user]) === false) {
            error_log($nostr_user . ' does not exists at ' . $nostr_domain);
            return new self(null, []);
        }
        return new self($json['names'][$nostr_user], $json['relays'] ?? []);
    }
}
