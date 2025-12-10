<?php

use nostriphant\NIP19\Bech32;

beforeAll(function() {
    // run php webserver
});

describe('webfinger', function() {
    it('responds with a 422 status code for a wrong domain (example.org)')
            ->get('/.well-known/webfinger?resource=acct%3Abob%example.org')
            ->status->toBe('422');
    
    it('responds with a 422 status code for a non-nostr user')
            ->get('/.well-known/webfinger?resource=acct%3Abob%nostripub.tld')
            ->status->toBe('422');
    
    it('responds with a 200 status code for a proper domain (nostripub.tld)')
            ->get('/.well-known/webfinger?resource=acct%3Anpub1efz8l77esdtpw6l359sjvakm7azvyv6mkuxphjdk3vfzkgxkatrqlpf9s4%40nostripub.tld')
            ->status->toBe('200')
            ->subject->toBe('acct:npub1efz8l77esdtpw6l359sjvakm7azvyv6mkuxphjdk3vfzkgxkatrqlpf9s4@nostripub.tld');
            
    it('responds for different user')
            ->get('/.well-known/webfinger?resource=acct%3Anpub16we82lw9jyxn3nqzafk4zsv7kt08f7csjut9q8t65aejvdde7ydsde8xln%40nostripub.tld')
            ->subject->toBe('acct:npub16we82lw9jyxn3nqzafk4zsv7kt08f7csjut9q8t65aejvdde7ydsde8xln@nostripub.tld');
    
    $public_key_hex = '7e7e9c42a91bfef19fa929e5fda1b72e0ebc1a4c1141673e2794234d86addf4e';
    $relays = ['wss://relay.nostr.example.mydomain.example.com', 'wss://nostr.banana.com'];

    $entities = [
        ['npub10elfcs4fr0l0r8af98jlmgdh9c8tcxjvz9qkw038js35mp4dma8qzvjptg', '200'],
        ['nsec1vl029mgpspedva04g90vltkh6fvh240zqtv9k0t9af8935ke9laqsnlfe5', '422'],
        [Bech32::nprofile(pubkey: $public_key_hex, relays: $relays), '422'],
        [Bech32::naddr(
            pubkey: $public_key_hex,
            relays: $relays,
            kind: 30023,
            identifier: 'banana'
        ), '422'],
        [Bech32::nevent(
                id: $public_key_hex,
                relays: $relays,
                kind: 30023,
        ), '422'],
    ];
    
    foreach ($entities as $entity) {
        it('responds with a '.$entity[1].' for nostr entity ' . $entity[0])
                ->get('/.well-known/webfinger?resource=acct%3A'.$entity[0].'%40nostripub.tld')
                ->status->toBe($entity[1]);
        
    }
    
    //it('only accepts npub... for now')->with($entities)->expect(fn(string $entity, $) => )
});

afterAll(function() {
    // close php webserver
});