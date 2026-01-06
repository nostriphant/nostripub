<?php

return function(string $path) {
  
    $urls = [
        '/.well-known/webfinger' => '/.well-known/webfinger.php'
    ];
    
    require __DIR__ . '/routes' . $urls[$path];
    
};

