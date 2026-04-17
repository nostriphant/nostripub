<?php

use nostriphant\nostripub\KeyRepository;

return fn() : nostriphant\nostripub\Endpoint => new class implements nostriphant\nostripub\Endpoint {
    #[\Override]
    public function __invoke(nostriphant\nostripub\Respond $respond) {
        $keys = new KeyRepository(CACHE_DIR . '/keys');
        $names = [];
        
        $key_files = glob(CACHE_DIR . '/keys/*.json');
        foreach ($key_files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (isset($data['public_key'])) {
                preg_match('/@(.+)$/', basename($file, '.json'), $matches);
                $identifier = hex2bin(basename($file, '.json'));
                $names[$identifier] = $data['public_key'];
            }
        }
        
        $requested_name = $_GET['name'] ?? null;

        if ($requested_name === null) {
            $respond(body:json_encode(['names' => $names]));
            return;
        }

        if (isset($names[$requested_name])) {
            $respond(body:json_encode(['names' => [$requested_name => $names[$requested_name]]]));
            return;
        }

        $respond(\nostriphant\nostripub\HTTPStatus::_404);
    }
};