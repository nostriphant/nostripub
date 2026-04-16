<?php

$router = require dirname(__DIR__) . '/routes.php';

$endpoint = $router($_GET['self']);

$endpoint(new nostriphant\nostripub\Respond);