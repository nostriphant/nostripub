<?php

return function(string $path) : nostriphant\nostripub\Endpoint {
    return require __DIR__ . '/routes' . $path . '.php';
};

