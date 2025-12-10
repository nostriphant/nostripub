<?php

require_once dirname(__DIR__) . '/bootstrap.php';

$requested_resource = $_GET['resource'];
if (str_ends_with($requested_resource, $_ENV['NOSTRIPUB_DOMAIN']) === false) {
    header('HTTP/1.1 422 Unprocessable Content', true);
    exit('Unprocessable Content');
}

list($scheme, $handle) = explode(':', $requested_resource, 2);
list($nostr_entity, $domain) = explode('@', $handle, 2);

try {
    $bech32 = new nostriphant\NIP19\Bech32($nostr_entity);
} catch (Exception $e) {
    header('HTTP/1.1 422 Unprocessable Content', true);
    exit('Unprocessable Content');
}



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
