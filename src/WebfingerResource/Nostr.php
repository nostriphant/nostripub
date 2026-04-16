<?php

namespace nostriphant\nostripub\WebfingerResource;

use nostriphant\nostripub\HTTP;
use nostriphant\nostripub\Respond;

readonly class Nostr {
    public function __construct(private string $baseurl, private \Closure $nip05_lookup) {
    }
    
    public function __invoke(string $handle, HTTP $http, Respond $respond): void {
        ($this->nip05_lookup)($handle, $respond)(function(\nostriphant\NIP01\Event $event) use ($respond, $handle, $baseurl) {
            $pubkey = $event->pubkey;

            $entity = [
                "subject" => 'nostr:' . $handle,
                "aliases" => [],
                "properties"=> [],
                "links" => [[
                    "rel" => "http://webfinger.net/rel/profile-page",
                    "href" => $this->baseurl . '/@'.$pubkey
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
}
