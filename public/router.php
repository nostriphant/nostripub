<?php

require_once dirname(__DIR__) . '/bootstrap.php';

$requested_resource = $_GET['resource'];
if (str_ends_with($requested_resource, $_ENV['NOSTRIPUB_DOMAIN']) === false) {
    header('HTTP/1.1 422 Unprocessable Content', true);
    exit('Unprocessable Content');
}

print '{
            "subject": "acct:bob@example.com",
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