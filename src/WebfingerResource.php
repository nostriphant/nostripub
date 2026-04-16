<?php


namespace nostriphant\nostripub;

final readonly class WebfingerResource {
    
    public function __construct(private string $browser_scheme, private string $browser_hostname, private \Closure $nip05_lookup) {
        
    }
    
    public function __invoke(string $requested_resource, HTTP $http, Respond $respond, KeyRepository $keys): void {
        list($scheme, $handle) = explode(':', $requested_resource, 2);
        $is_activitypub_user = false;
        
        if ($scheme === 'acct') {
            $resource = new WebfingerResource\Acct($handle, $keys);
        } elseif ($scheme === 'nostr') {
            $resource = new WebfingerResource\Nostr($handle, $this->nip05_lookup);
        } else {
            $respond(HTTPStatus::_400);
        }
        
        $resource($this->browser_scheme.'://'.$this->browser_hostname, $http, $respond);
        
    }
    
}
