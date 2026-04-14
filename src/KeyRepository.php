<?php

namespace nostriphant\nostripub;

use nostriphant\NIP01\Key;

final readonly class KeyRepository {

    public function __construct(private string $directory) {
        
    }

    public function __invoke(string $identifier): array {
        $path = $this->directory . '/' . md5($identifier) . '.json';
        if (file_exists($path) === false) {
            $key = Key::generate();
            $private_key_hex = $key(Key::private());
            $public_key_hex = Key::public()($private_key_hex);

            file_put_contents($path, json_encode([
                'private_key' => $private_key_hex,
                'public_key' => $public_key_hex,
            ]));
        }

        return json_decode(file_get_contents($path), true);
    }
}
