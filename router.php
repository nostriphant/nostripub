<?php

require_once __DIR__ . '/bootstrap.php';
    
$router = require __DIR__ . '/routes.php';

$endpoint = $router($_SERVER['PHP_SELF']);

$endpoint(function(\nostriphant\nostripub\HTTPStatus $status = \nostriphant\nostripub\HTTPStatus::_200, array $headers = [], ?string $body = null) {
    header('HTTP/1.1 '.substr($status->name, 1).' ' . $status->value, true);
    foreach ($headers as $header) {
        header($header, true);
    }
    exit($body ?? $status->value);
});