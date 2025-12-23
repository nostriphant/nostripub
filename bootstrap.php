<?php

require_once __DIR__ . '/vendor/autoload.php';

define('CACHE_DIR', __DIR__ . '/cache');

$dotenv_file = __DIR__ . '/.env';
is_file($dotenv_file) || touch($dotenv_file);
$dotenv = Dotenv\Dotenv::createMutable(dirname($dotenv_file));
$dotenv->load();