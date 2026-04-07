<?php

use nostriphant\nostripub\NIP05;

return new class implements nostriphant\nostripub\Endpoint {
    #[\Override]
    public function __invoke(nostriphant\nostripub\Respond $respond) {
        if (isset($_GET['resource']) === false) {
            $respond(\nostriphant\nostripub\HTTPStatus::_400);
        }

        $browser_hostname = $_SERVER["HTTP_HOST"];
        $browser_scheme = 'http'. ($_SERVER['HTTPS'] ?? 'off' !== 'off' ? 's' : '');

        $discovery_relays = array_map(fn(string $relay) => 'wss://'. $relay, array_filter($_ENV, fn(string $key) => str_starts_with($key, 'DISCOVERY_RELAY'), ARRAY_FILTER_USE_KEY));
        $requested_resource = $_GET['resource'];

        $http = new \nostriphant\nostripub\HTTP(CACHE_DIR);

        $webfinger = new \nostriphant\nostripub\WebfingerResource($browser_hostname, NIP05::lookup($discovery_relays, $http, $respond));

        $nip05 = $webfinger($requested_resource);

        $nip05(function(\nostriphant\NIP01\Event $event) use ($respond, $requested_resource, $browser_scheme, $browser_hostname) {
            $entity = [
                "subject" => $requested_resource,
                "aliases" => [],
                "properties"=> [],
                "links" => [[
                    "rel" => "http://webfinger.net/rel/profile-page",
                    "href" => $browser_scheme.'://'.$browser_hostname.'/@'.$event->pubkey
                ]]
            ];

            $profile = json_decode($event->content);
            if ($profile && isset($profile->picture) && $profile->picture) {
                $curl = curl_init($profile->picture);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_exec($curl);
                $info = curl_getinfo($curl);
                curl_close($curl);

                $entity['links'][] = [
                    "rel" => "http://webfinger.net/rel/avatar",
                    "type" => $info['content_type'],
                    "href" => $info['url']
                ];
            }

            $respond(headers:['Content-Type: application/jrd+json'], body:json_encode($entity));
        });

    }
};
