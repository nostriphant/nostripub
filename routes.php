<?php
require_once __DIR__ . '/bootstrap.php';

return function(string $path) : nostriphant\nostripub\Endpoint {
    return require __DIR__ . '/routes' . $path . '.php';
};

