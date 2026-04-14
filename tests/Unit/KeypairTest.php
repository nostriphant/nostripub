<?php

use nostriphant\nostripub\Keypair;

beforeEach(function() {
    $this->directory = CACHE_DIR . '/keys/' . uniqid();
    mkdir($this->directory, 0755, true);
    $this->keypair = new Keypair($this->directory);
});

afterEach(function() {
    $files = glob($this->directory . '/*');
    foreach ($files as $file) {
        unlink($file);
    }
    rmdir($this->directory);
});

describe('Keypair', function() {
    it('generates a keypair for an identifier', function() {
        $keypair = $this->keypair;
        $keys = $keypair('test@example.com');
        expect($keys)->toBeArray();
        expect(array_keys($keys))->toEqual(['private_key', 'public_key']);
        expect($keys['private_key'])->toStartWith('nsec1');
        expect($keys['public_key'])->toStartWith('npub1');
    });
    
    it('returns the same keypair for the same identifier', function() {
        $keypair = $this->keypair;
        $keys1 = $keypair('test@example.com');
        $keys2 = $keypair('test@example.com');
        expect($keys1)->toEqual($keys2);
    });
    
    it('stores different keypairs for different identifiers', function() {
        $keypair = $this->keypair;
        $keys1 = $keypair('user1@example.com');
        $keys2 = $keypair('user2@example.com');
        expect($keys1['private_key'])->not->toBe($keys2['private_key']);
        expect($keys1['public_key'])->not->toBe($keys2['public_key']);
    });
    
    it('stores valid bech32 encoded keys', function() {
        $keypair = $this->keypair;
        $keys = $keypair('test@example.com');
        expect($keys['private_key'])->toMatch('/^nsec1[023456789acdefghjklmnpqrstuvwxyz]{58}$/');
        expect($keys['public_key'])->toMatch('/^npub1[023456789acdefghjklmnpqrstuvwxyz]{58}$/');
    });
    
    it('creates file for stored keypair', function() {
        $keypair = $this->keypair;
        $identifier = 'test@example.com';
        $keys = $keypair($identifier);
        
        $files = glob($this->directory . '/*.json');
        expect($files)->toHaveCount(1);
        
        $stored = json_decode(file_get_contents($files[0]), true);
        expect($stored)->toEqual($keys);
    });
});

describe('nostr.json names mapping', function() {
    it('maps generated keys to identifier names', function() {
        $keypair = $this->keypair;
        $identifier = 'rik@rikmeijer.nl';
        $keys = $keypair($identifier);
        
        $files = glob($this->directory . '/*.json');
        expect($files)->toHaveCount(1);
        
        $stored = json_decode(file_get_contents($files[0]), true);
        expect($stored)->toEqual($keys);
        expect($stored['public_key'])->toStartWith('npub1');
    });
    
    it('serves public keys for nostr.json endpoint', function() {
        $keypair = $this->keypair;
        $identifier = 'testuser@example.com';
        $keys = $keypair($identifier);
        
        expect($keys['public_key'])->toStartWith('npub1');
        expect($keys['private_key'])->toStartWith('nsec1');
    });
});
