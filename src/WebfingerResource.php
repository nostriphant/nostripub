<?php


namespace nostriphant\nostripub;

final readonly class WebfingerResource {
    
    public function __construct(private string $browser_scheme, private string $browser_hostname, private \Closure $nip05_lookup) {
        
    }
    
    public function __invoke(string $requested_resource, HTTP $http, Respond $respond, KeyRepository $keys): void {
        list($scheme, $handle) = explode(':', $requested_resource, 2);
        $is_activitypub_user = false;
        
        if ($scheme === 'acct') {
            list($user, $domain) = explode('@', $handle, 2);
            if ($domain !== $this->browser_hostname) {
                $respond(HTTPStatus::_302, ['Location: https://' . $domain . '/.well-known/webfinger?resource=acct:' . urlencode($handle)]);
                return;
            }
            
            $handle = str_replace('.at.', '@', $user);
            list($user, $domain) = explode('@', $handle, 2);
            $activity_pub_account = $http('https://' . $domain . '/.well-known/webfinger?resource=acct:' . urlencode($handle), $respond);
            
            $keys = $keys($handle);
            $pubkey = $keys['public_key'];
            
            $activity_pub_account['subject'] = $requested_resource;
            $activity_pub_account['aliases'][] = $this->browser_scheme.'://'.$this->browser_hostname.'/@'.$pubkey;
            
            $respond(headers:['Content-Type: application/jrd+json'], body:json_encode($activity_pub_account));
        } elseif ($scheme === 'nostr') {
            ($this->nip05_lookup)($handle, $respond)(function(\nostriphant\NIP01\Event $event) use ($respond, $requested_resource, $handle, $keys) {
                $pubkey = $event->pubkey;

                $entity = [
                    "subject" => $requested_resource,
                    "aliases" => [],
                    "properties"=> [],
                    "links" => [[
                        "rel" => "http://webfinger.net/rel/profile-page",
                        "href" => $this->browser_scheme.'://'.$this->browser_hostname.'/@'.$pubkey
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
        
        $respond(HTTPStatus::_400);
    }
    
}
