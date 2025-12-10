<?php

beforeAll(function() {
    // run php webserver
});

describe('webfinger', function() {
    it('responds with a 422 status code for a wrong domain (example.org)')
            ->get('/.well-known/webfinger?resource=acct%3Abob%example.org')
            ->bundleExpectations(function(string $status, string $content) {
        expect($status)->toBe('422');
    });
    it('responds with a 422 status code for a non-nostr user')
            ->get('/.well-known/webfinger?resource=acct%3Abob%nostripub.tld')
            ->bundleExpectations(function(string $status, string $content) {
        expect($status)->toBe('422');
    });
    
    it('responds with a 200 status code for a proper domain (nostripub.tld)')
            ->get('/.well-known/webfinger?resource=acct%3Anpub1efz8l77esdtpw6l359sjvakm7azvyv6mkuxphjdk3vfzkgxkatrqlpf9s4%40nostripub.tld')
            ->bundleExpectations(function(string $status, string $content) {
        expect($status)->toBe('200');
        expect($content)->toBeJson();
        $json = json_decode($content, true);
        expect($json['subject'])->toBe('acct:npub1efz8l77esdtpw6l359sjvakm7azvyv6mkuxphjdk3vfzkgxkatrqlpf9s4@nostripub.tld');
    });
    it('responds for different user')
            ->get('/.well-known/webfinger?resource=acct%3Anpub16we82lw9jyxn3nqzafk4zsv7kt08f7csjut9q8t65aejvdde7ydsde8xln%40nostripub.tld')
            ->bundleExpectations(function(string $status, string $content) {
        expect($content)->toBeJson();
        $json = json_decode($content, true);
        expect($json['subject'])->toBe('acct:npub16we82lw9jyxn3nqzafk4zsv7kt08f7csjut9q8t65aejvdde7ydsde8xln@nostripub.tld');
    });
});

afterAll(function() {
    // close php webserver
});