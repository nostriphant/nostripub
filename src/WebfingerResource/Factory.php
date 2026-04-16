<?php

namespace nostriphant\nostripub\WebfingerResource;

readonly class Factory {
    

    public function __construct(private string $baseurl, private \nostriphant\nostripub\KeyRepository $keys, private \Closure $nip05_lookup, private \nostriphant\nostripub\Respond $respond) {
        
    }

    public function __invoke(string $scheme): Acct|Nostr {
        return match ($scheme) {
            'acct' => new \nostriphant\nostripub\WebfingerResource\Acct($this->baseurl, $this->keys),
            'nostr' => new \nostriphant\nostripub\WebfingerResource\Nostr($this->baseurl, $this->nip05_lookup),
            default => ($this->respond)(\nostriphant\nostripub\HTTPStatus::_400)
        };
    }
}
