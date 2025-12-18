<?php
require_once dirname(__DIR__) . '/bootstrap.php';

use nostriphant\nostripub\NIP05;

$browser_hostname = $_SERVER["HTTP_HOST"];
$browser_scheme = 'http'. ($_SERVER['HTTPS'] ?? 'off' !== 'off' ? 's' : '');
$discovery_relays = array_map(fn(string $relay) => 'wss://'. $relay,[
    'indexer.coracle.social',
    'relay.nostr.band',
    'relay.mostr.pub',
    'relay.noswhere.com'
]);

if (isset($_GET['resource']) === false) {
    header('HTTP/1.1 400 Bad Request', true);
    exit('Bad Request');
    
}
$requested_resource = $_GET['resource'];

list($scheme, $handle) = explode(':', $requested_resource, 2);

$http = new \nostriphant\nostripub\HTTP(dirname(__DIR__) . '/cache');

$nip05_lookup = NIP05::lookup($discovery_relays, $http, function() {
    header('HTTP/1.1 404 Not found', true);
    return 'Not found';
});

switch ($scheme) {
    case 'acct':
        list($user, $domain) = explode('@', $handle, 2);
        if ($domain !== $browser_hostname) {
            header('HTTP/1.1 302 Found', true);
            header('Location: https://' . $domain . '/.well-known/webfinger?resource=' . urlencode($requested_resource));
            exit('Found');
        }
        $nip05 = $nip05_lookup(str_replace('.at.', '@', $user));
        break;
        
    case 'nostr':
        if (str_contains($handle, '@')) {
            $nip05 = $nip05_lookup($handle);
        } elseif (str_starts_with($handle, 'npub1')) {
            $nip05 = new NIP05(new nostriphant\NIP19\Bech32($handle), $discovery_relays);
            break;
        } else {
            header('HTTP/1.1 422 Unprocessable Content', true);
            exit('Unprocessable Content');
        }
        break;
}
    
$metadata = $nip05(function() {
    header('HTTP/1.1 422 Unprocessable Content', true);
    return 'Unprocessable Content';
});

$entity = [
    "subject" => $requested_resource,
    "aliases" => [],
    "properties"=> [],
    "links" => []
];

$entity['links'] = [[
        "rel" => "http://webfinger.net/rel/profile-page",
        "href" => $browser_scheme.'://'.$browser_hostname.'/@'.$metadata->pubkey
]];

if (\nostriphant\NIP01\Event::hasTag($metadata, "picture")) {
    $avatar_url = \nostriphant\NIP01\Event::extractTagValues($metadata, "picture")[0][0];
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
print json_encode($entity);