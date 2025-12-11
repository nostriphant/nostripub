<?php

require_once dirname(__DIR__) . '/bootstrap.php';

$requested_resource = $_GET['resource'];
list($scheme, $handle) = explode(':', $requested_resource, 2);
list($nostr_user, $nostr_domain) = explode('@', $handle, 2);

$curl = curl_init('https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$body = curl_exec($curl);
$info = curl_getinfo($curl);
curl_close($curl);

if ($info['http_code'] !== 200) {
    header('HTTP/1.1 404 Not found', true);
    exit('Not found');
}

$json = json_decode($body, true);
if (isset($json['names'][$nostr_user]) === false) {
    header('HTTP/1.1 404 Not found', true);
    exit('Not found');
}

try {
    $bech32 = nostriphant\NIP19\Bech32::npub($json['names'][$nostr_user]);
} catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Content', true);
    exit('Unprocessable Content');
}

switch ($bech32->type) {
    case 'npub':
        //$public_key_hex = $bech32();
        //$public_key_bech32 = (string) (Bech32::npub($public_key_hex));

        print '{
                    "subject": "'.$requested_resource.'",
                    "aliases": [
                            "https://www.example.com/~bob/"
                    ],
                    "properties": {
                            "http://example.com/ns/role": "employee"
                    },
                    "links": [
                            {
                                    "rel": "http://webfinger.net/rel/profile-page",
                                    "href": "https://www.example.com/~bob/"
                            },
                            {
                                    "rel": "http://webfinger.net/rel/avatar",
                                    "type": "image/png",
                                    "href": "https://www.example.com/~bob/avatar.png"
                            }
                    ]
            }';

        break;
    
    default:
        header('HTTP/1.1 422 Unprocessable Content', true);
        exit('Unprocessable Content');
}