<?php

require_once dirname(dirname(__DIR__)) . '/bootstrap.php';

use nostriphant\nostripub\NIP05;

if (isset($_GET['resource']) === false) {
    header('HTTP/1.1 400 Bad Request', true);
    exit('Bad Request');
}

$browser_hostname = $_SERVER["HTTP_HOST"];
$browser_scheme = 'http'. ($_SERVER['HTTPS'] ?? 'off' !== 'off' ? 's' : '');

$discovery_relays = array_map(fn(string $relay) => 'wss://'. $relay, array_filter($_ENV, fn(string $key) => str_starts_with($key, 'DISCOVERY_RELAY'), ARRAY_FILTER_USE_KEY));
$requested_resource = $_GET['resource'];

$http = new \nostriphant\nostripub\HTTP(CACHE_DIR);

$webfinger = new \nostriphant\nostripub\WebfingerResource($browser_hostname, NIP05::lookup($discovery_relays, $http, function(string $code) {
    $message = match($code) {
        '422' => 'Unprocessable Content',
        '404' => 'Not found'
    };
    header('HTTP/1.1 ' . $code . ' ' . $message, true);
    return $message;
}));

$nip05 = $webfinger($requested_resource);

$nip05(function(\nostriphant\NIP01\Event $event) use ($requested_resource, $browser_scheme, $browser_hostname) {
    $entity = [
        "subject" => $requested_resource,
        "aliases" => [],
        "properties"=> [],
        "links" => [[
            "rel" => "http://webfinger.net/rel/profile-page",
            "href" => $browser_scheme.'://'.$browser_hostname.'/@'.$event->pubkey
        ]]
    ];

    if (\nostriphant\NIP01\Event::hasTag($event, "picture")) {
        $avatar_url = \nostriphant\NIP01\Event::extractTagValues($event, "picture")[0][0];
        $curl = curl_init($avatar_url);
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

    header('Content-Type: application/jrd+json', true);
    exit(json_encode($entity));
}, function() {
    header('HTTP/1.1 422 Unprocessable Content', true);
    return 'Unprocessable Content';
});
