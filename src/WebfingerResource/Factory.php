<?php

namespace nostriphant\nostripub\WebfingerResource;

use nostriphant\nostripub\NIP05;
use nostriphant\nostripub\KeyRepository;

readonly class Factory {
    

    public function __construct(private string $baseurl, private \nostriphant\nostripub\HTTP $http, private \nostriphant\nostripub\Respond $respond) {
        
    }

    public function __invoke(string $scheme): Acct|Nostr {
        $keys_Directory = CACHE_DIR . '/keys';
        is_dir($keys_Directory) || mkdir($keys_Directory);
        
        return match ($scheme) {
            'acct' => new \nostriphant\nostripub\WebfingerResource\Acct($this->baseurl, new KeyRepository($keys_Directory), $this->http, $this->respond),
            'nostr' => new \nostriphant\nostripub\WebfingerResource\Nostr($this->baseurl, NIP05::lookup($this->http), $this->respond),
            default => ($this->respond)(\nostriphant\nostripub\HTTPStatus::_400)
        };
    }
}
