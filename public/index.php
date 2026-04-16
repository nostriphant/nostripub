<?php

$router = require __DIR__ . '/routes.php';

$endpoint = $router($_GET['self']);

$endpoint(new nostriphant\nostripub\Respond);