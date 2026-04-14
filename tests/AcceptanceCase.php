<?php

namespace nostriphant\nostripubTests;

use PHPUnit\Framework\TestCase as BaseTestCase;


abstract class AcceptanceCase extends BaseTestCase
{
    
    public static Process $process;
   
 
    public function get(string $path) {
        $curl = curl_init('http://127.0.0.1:8080' . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        
        return expect(new class($info['http_code'], $info['http_code'] === 200 ? json_decode($body, true) : null) {
            public ?string $subject;
            public ?array $links;
            public ?array $aliases;
            public function __construct(public string $status, ?array $content) {
                $this->subject = $content['subject'] ?? null;
                $this->links = $content['links'] ?? null;
                $this->aliases = $content['aliases'] ?? null;
            }
        });
    }
    
   
}