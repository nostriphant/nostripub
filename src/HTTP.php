<?php

namespace nostriphant\nostripub;

final readonly class HTTP {
    public function __construct(private string $cache) {
        
    }
    
    public function __invoke(string $url, callable $error) : array {
        $cache_file = $this->cache . '/'. md5($url);
        if (file_exists($cache_file . '.json')) {
            $body = file_get_contents($cache_file . '.json');
        } elseif (file_exists($cache_file . '.error')) {
            exit($error('404'));

        } else {
            error_log('Requesting ' . $url);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $body = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);

            if ($info['http_code'] !== 200) {
                touch($cache_file . '.error');
                exit($error('404'));
            }

            file_put_contents($cache_file . '.json', $body);
        }

        return json_decode($body, true); 

    }
}
