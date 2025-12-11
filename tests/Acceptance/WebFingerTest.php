<?php

use nostriphant\NIP19\Bech32;

beforeAll(function() {
    // run php webserver
});

describe('webfinger', function() {
    it('responds with a 404 status code for a non-existing (or unretrievable) NIP-05 identifier, because wrong domain')
            ->get('/.well-known/webfinger?resource=acct%3Abob%40example.tlb')
            ->status->toBe('404');
    
    it('responds with a 404 status code for a non-existing (or unretrievable) NIP-05 identifier, because non-existing user')
            ->get('/.well-known/webfinger?resource=acct%3Abob%40example.org')
            ->status->toBe('404');
    
    it('responds with a 200 status code for an existing NIP-05 identifier')
            ->get('/.well-known/webfinger?resource=acct%3Arik%40rikmeijer.nl')
            ->status->toBe('200')
            ->subject->toBe('acct:rik@rikmeijer.nl');
            
    it('responds for different user')
            ->get('/.well-known/webfinger?resource=acct%3Anostriphant%40rikmeijer.nl')
            ->subject->toBe('acct:nostriphant@rikmeijer.nl');
});

afterAll(function() {
    // close php webserver
});