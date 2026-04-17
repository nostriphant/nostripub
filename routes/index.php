<?php


return fn(string $resource_identifier) : nostriphant\nostripub\Endpoint => new class($resource_identifier) implements nostriphant\nostripub\Endpoint {
    
            public function __construct(private string $pubkey) {
                ;
            }
    
            #[\Override]
            public function __invoke(nostriphant\nostripub\Respond $respond) {
                $identifier = \nostriphant\nostripub\KeyRepository::findByPubkey(CACHE_DIR . '/keys', substr($this->pubkey, 1));
                $respond(body:$identifier);
            }
};