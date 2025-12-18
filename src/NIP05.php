<?php

namespace nostriphant\nostripub;

final readonly class NIP05 {
    
    public function __construct(private \nostriphant\NIP19\Bech32 $pubkey, private ?array $relays) {
        
    }
    
    public static function lookup(string $nostr_user, string $nostr_domain, callable $error) : self {
        error_log('Requesting https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user);
        $curl = curl_init('https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($info['http_code'] !== 200) {
            exit($error());
        }

        $json = json_decode($body, true);
        if (isset($json['names'][$nostr_user]) === false) {
            exit($error());
        }
        return new self(\nostriphant\NIP19\Bech32::npub($json['names'][$nostr_user]), $json['relays'] ?? null);
    }
    
    public function __invoke(array $discovery_relays, callable $error): \nostriphant\NIP01\Event {
        return \nostriphant\nostripub\Metadata::discoverByNpub($this->pubkey, $this->relays ?? $discovery_relays);
    }
}
