<?php
require_once __DIR__ . '/bootstrap.php';

use nostriphant\nostripub\Endpoint;

return function(string $path) : Endpoint {
    $route_file = __DIR__ . '/routes' . $path . '.php';
    if (is_file($route_file) === false) {
        $route_directory = dirname($route_file);
        if (is_file($route_directory . '/index.php')) {
            return (require $route_directory . '/index.php')(basename($path));
        }
        
        return new class implements Endpoint {
            #[\Override]
            public function __invoke(nostriphant\nostripub\Respond $respond) {
                $respond(\nostriphant\nostripub\HTTPStatus::_404);
            }
        };
    }
    return (require $route_file)(basename($path));
};

