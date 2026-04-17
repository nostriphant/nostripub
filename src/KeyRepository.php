<?php

namespace nostriphant\nostripub;

use nostriphant\NIP01\Key;

final readonly class KeyRepository {

    public function __construct(private string $directory) {
    }
    
    static function findByPubkey(string $directory, string $pubkey) : ?string {
        foreach (glob($directory . '/*.json') as $path) {
            $file_contents = json_decode(file_get_contents($path), true);
            if (($file_contents['public_key'] ?? null) === $pubkey) {
                return $file_contents['identifier'] ?? null;
            }
        }
        return null;
    }

    public function __invoke(string $identifier): array {
        $path = $this->directory . '/' . md5($identifier) . '.json';
        if (file_exists($path)) {
            $file_contents = json_decode(file_get_contents($path), true);
        } else {
            $key = Key::generate();
            $private_key_hex = $key(Key::private());
            $public_key_hex = Key::public()($private_key_hex);
            $file_contents = [
                'private_key' => $private_key_hex,
                'public_key' => $public_key_hex,
            ];
        }
        
        if (isset($file_contents['identifier']) === false) {
            $file_contents['identifier'] = $identifier;
        }

        
        file_put_contents($path, json_encode($file_contents));
        
        return $file_contents;
    }
}
