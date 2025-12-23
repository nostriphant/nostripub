<?php

$urls = [
    '/.well-known/webfinger' => '/.well-known/webfinger.php'
];

require __DIR__ . '/public/' . $urls[$_SERVER['PHP_SELF']];