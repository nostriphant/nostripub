<?php

namespace nostriphant\nostripub;

final readonly class WebfingerResource {

    public function __construct(private WebfingerResource\Factory $scheme_factory) {
        
    }

    public function __invoke(string $requested_resource, HTTP $http, Respond $respond): void {
        list($scheme, $handle) = explode(':', $requested_resource, 2);
        $resource = ($this->scheme_factory)($scheme);
        $resource($handle, $http, $respond);
    }
}
