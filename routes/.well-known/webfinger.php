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
        $http = new \nostriphant\nostripub\HTTP(CACHE_DIR);
        
        $nip05_lookup = NIP05::lookup($http);
        
        $keys_Directory = CACHE_DIR . '/keys';
        is_dir($keys_Directory) || mkdir($keys_Directory);
        $keys = new KeyRepository($keys_Directory);
                
        $browser_scheme = 'http'. ($_SERVER['HTTPS'] ?? 'off' !== 'off' ? 's' : '');
        $browser_hostname = $_SERVER["HTTP_HOST"];
        $baseurl = $browser_scheme . '://' . $browser_hostname;
        $factory = new nostriphant\nostripub\WebfingerResource\Factory($baseurl, $keys, $nip05_lookup, $respond);
        
        $webfinger = new \nostriphant\nostripub\WebfingerResource($factory);

        $webfinger($_GET['resource'], $http, $respond);

    }
};
