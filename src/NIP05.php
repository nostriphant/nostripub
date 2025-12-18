<?php

namespace nostriphant\nostripub;

final readonly class NIP05 {
    
    public function __construct(private \nostriphant\NIP19\Bech32 $pubkey, private array $relays) {
        
    }
    
    public static function lookup(array $discovery_relays, callable $http, callable $error) : callable {
        return function(string $nip05_identifier) use ($discovery_relays, $http, $error) : self {
            list($nostr_user, $nostr_domain) = explode('@', $nip05_identifier, 2);
            $json = $http('https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user, $error);
            if (isset($json['names'][$nostr_user]) === false) {
                exit($error());
            }
            return new self(\nostriphant\NIP19\Bech32::npub($json['names'][$nostr_user]), $json['relays'] ?? $discovery_relays);
        };
    }
    
    public function __invoke(callable $error): \nostriphant\NIP01\Event {
        return \nostriphant\nostripub\Metadata::discoverByNpub($this->pubkey, $this->relays);
    }
}
