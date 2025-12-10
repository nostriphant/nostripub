<?php

beforeAll(function() {
    // run php webserver
});

describe('webfinger', function() {
    it('responds with a 200 status code for a proper domain (nostripub.tld)')->get('/.well-known/webfinger?resource=acct%3Abob%40nostripub.tld')->assertStatus('200');
    it('responds with a 422 status code for a wrong domain (example.org)')->get('/.well-known/webfinger?resource=acct%3Abob%example.org')->assertStatus('422');

        /**
         * {
            "subject": "acct:bob@example.com",
            "aliases": [
                    "https://www.example.com/~bob/"
            ],
            "properties": {
                    "http://example.com/ns/role": "employee"
            },
            "links": [
                    {
                            "rel": "http://webfinger.net/rel/profile-page",
                            "href": "https://www.example.com/~bob/"
                    },
                    {
                            "rel": "http://webfinger.net/rel/avatar",
                            "type": "image/png",
                            "href": "https://www.example.com/~bob/avatar.png"
                    }
            ]
    }
         */
    
});

afterAll(function() {
    // close php webserver
});