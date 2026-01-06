<?php

return function(string $path) {
    require __DIR__ . '/routes' . $path . '.php';
};

