<?php

use nostriphant\nostripub\NIP05;
use nostriphant\nostripub\KeyRepository;

return new class implements nostriphant\nostripub\Endpoint {
    #[\Override]
    public function __invoke(nostriphant\nostripub\Respond $respond) {
        if (isset($_GET['resource']) === false) {
            $respond(\nostriphant\nostripub\HTTPStatus::_400);
            return;
        }

        $discovery_relays = array_map(fn(string $relay) => 'wss://'. $relay, array_filter($_ENV, fn(string $key) => str_starts_with($key, 'DISCOVERY_RELAY'), ARRAY_FILTER_USE_KEY));
        $http = new \nostriphant\nostripub\HTTP(CACHE_DIR);
        $nip05_lookup = NIP05::lookup($discovery_relays, $http);
        
        $keys_Directory = CACHE_DIR . '/keys';
        is_dir($keys_Directory) || mkdir($keys_Directory);
        $keys = new KeyRepository($keys_Directory);
                
        $browser_scheme = 'http'. ($_SERVER['HTTPS'] ?? 'off' !== 'off' ? 's' : '');
        $browser_hostname = $_SERVER["HTTP_HOST"];
        $baseurl = $browser_scheme . '://' . $browser_hostname;
        $webfinger = new \nostriphant\nostripub\WebfingerResource(function(string $scheme) use ($baseurl, $keys, $nip05_lookup, $respond) {
            if ($scheme === 'acct') {
                return new \nostriphant\nostripub\WebfingerResource\Acct($baseurl, $keys);
            } elseif ($scheme === 'nostr') {
                return new \nostriphant\nostripub\WebfingerResource\Nostr($baseurl, $nip05_lookup);
            } else {
                $respond(\nostriphant\nostripub\HTTPStatus::_400);
            }
        });

        $webfinger($_GET['resource'], $http, $respond);

    }
};
