<?php


namespace nostriphant\nostripub;

final readonly class WebfingerResource {
    
    public function __construct(private string $browser_hostname, private \Closure $nip05_lookup) {
        
    }
    
    public function __invoke(string $requested_resource): NIP05 {
        list($scheme, $handle) = explode(':', $requested_resource, 2);
        if ($scheme === 'acct') {
            list($user, $domain) = explode('@', $handle, 2);
            if ($domain !== $this->browser_hostname) {
                header('HTTP/1.1 302 Found', true);
                header('Location: https://' . $domain . '/.well-known/webfinger?resource=acct:' . urlencode($handle));
                exit('Found');
            }
            $handle = str_replace('.at.', '@', $user);
        }
        
        return ($this->nip05_lookup)($handle);
    }
    
}
