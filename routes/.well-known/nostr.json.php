<?php


return new class implements nostriphant\nostripub\Endpoint {
    #[\Override]
    public function __invoke() {
        $names = [];
        $requested_name = $_GET['name'] ?? null;

        if ($requested_name === null) {
            exit(json_encode(['names' => $names]));
        }

        if (isset($names[$requested_name])) {
            exit(json_encode(['names' => [$requested_name => $names[$requested_name]]]));
        }

        header('HTTP/1.1 404 Not Found', true);
        exit('Not Found');
    }
};