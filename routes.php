<?php
require_once __DIR__ . '/bootstrap.php';

return function(string $path) : nostriphant\nostripub\Endpoint {
    $route_file = __DIR__ . '/routes' . $path . '.php';
    if (is_file($route_file) === false) {
        return new class implements nostriphant\nostripub\Endpoint {
            #[\Override]
            public function __invoke(nostriphant\nostripub\Respond $respond) {
                $respond(\nostriphant\nostripub\HTTPStatus::_404);
            }
        };
    }
    return require $route_file;
};

