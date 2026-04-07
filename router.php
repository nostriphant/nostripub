<?php

require_once __DIR__ . '/bootstrap.php';
    
$router = require __DIR__ . '/routes.php';

$endpoint = $router($_SERVER['PHP_SELF']);

$endpoint();