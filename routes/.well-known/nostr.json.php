<?php


return new class implements nostriphant\nostripub\Endpoint {
    #[\Override]
    public function __invoke(nostriphant\nostripub\Respond $respond) {
        $names = [];
        $requested_name = $_GET['name'] ?? null;

        if ($requested_name === null) {
            $respond(body:json_encode(['names' => $names]));
        }

        if (isset($names[$requested_name])) {
            $respond(body:json_encode(['names' => [$requested_name => $names[$requested_name]]]));
        }

        $respond(\nostriphant\nostripub\HTTPStatus::_404);
    }
};