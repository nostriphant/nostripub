<?php

use nostriphant\NIP19\Bech32;

beforeAll(function() {
    // run php webserver
});

describe('webfinger', function() {
    it('responds with a 400 status code for a missing resource')
            ->get('/.well-known/webfinger')
            ->status->toBe('400');
    
    it('responds with a 302 status code for a resource at a different domain')
            ->get('/.well-known/webfinger?resource=acct%3Arik%40rikmeijer.nl')
            ->status->toBe('302');
    
    
    it('responds with a 200 status code for a resource at a different wrapped domain')
            ->get('/.well-known/webfinger?resource=acct%3Arik.at.rikmeijer.nl%40127.0.0.1:8080')
            ->status->toBe('200')
            ->subject->toBe('acct:rik.at.rikmeijer.nl@127.0.0.1:8080')
            ->links->toBe([
                            [
                                    "rel" => "http://webfinger.net/rel/profile-page",
                                    "href" =>  "http://127.0.0.1:8080/@npub1efz8l77esdtpw6l359sjvakm7azvyv6mkuxphjdk3vfzkgxkatrqlpf9s4"
                            ],
                            [
                                    "rel" => "http://webfinger.net/rel/avatar",
                                    "type"=> "image/png",
                                    "href"=> "https://gravatar.com/userimage/128219001/7b07009f6c5aff6f13b1050c1b354208.jpeg?size=256"
                            ]
                    ]);
    
    it('responds with a 404 status code for a non-existing (or unretrievable) NIP-05 identifier, because wrong domain')
            ->get('/.well-known/webfinger?resource=nostr%3Abob%40example.tlb')
            ->status->toBe('404');
    
    it('responds with a 404 status code for a non-existing (or unretrievable) NIP-05 identifier, because non-existing user')
            ->get('/.well-known/webfinger?resource=nostr%3Abob%40example.org')
            ->status->toBe('404');
    
    it('responds with a 200 status code for an existing NIP-05 identifier (rik@rikmeijer.nl) in a nostr scheme (NIP-21)')
            ->get('/.well-known/webfinger?resource=nostr%3Arik%40rikmeijer.nl')
            ->status->toBe('200')
            ->subject->toBe('nostr:rik@rikmeijer.nl')
            ->links->toBe([
                            [
                                    "rel" => "http://webfinger.net/rel/profile-page",
                                    "href" =>  "http://127.0.0.1:8080/@npub1efz8l77esdtpw6l359sjvakm7azvyv6mkuxphjdk3vfzkgxkatrqlpf9s4"
                            ],
                            [
                                    "rel" => "http://webfinger.net/rel/avatar",
                                    "type"=> "image/png",
                                    "href"=> "https://gravatar.com/userimage/128219001/7b07009f6c5aff6f13b1050c1b354208.jpeg?size=256"
                            ]
                    ]);
            
    it('responds with a 200 status code for a different existing NIP-05 identifier (nostriphant@rikmeijer.nl) in a nostr scheme (NIP-21)')
            ->get('/.well-known/webfinger?resource=nostr%3Anostriphant%40rikmeijer.nl')
            ->status->toBe('200')
            ->subject->toBe('nostr:nostriphant@rikmeijer.nl')
            ->links->toBe([
                            [
                                    "rel" => "http://webfinger.net/rel/profile-page",
                                    "href" =>  "http://127.0.0.1:8080/@npub16we82lw9jyxn3nqzafk4zsv7kt08f7csjut9q8t65aejvdde7ydsde8xln"
                            ],
                            [
                                    "rel" => "http://webfinger.net/rel/avatar",
                                    "type"=> "image/png",
                                    "href"=> "https://avatars.githubusercontent.com/u/186454238?s=200&v=4"
                            ]
                    ]);;
                    
                    
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
        it('responds with a '.$entity[1].' for NIP-19 nostr entity ' . $entity[0])
                ->get('/.well-known/webfinger?resource=nostr%3A'.$entity[0])
                ->status->toBe($entity[1]);
        
    }
    
    //it('only accepts npub... for now')->with($entities)->expect(fn(string $entity, $) => )
});

afterAll(function() {
    // close php webserver
});