<?php

namespace nostriphant\nostripubTests;

use PHPUnit\Framework\TestCase as BaseTestCase;


abstract class AcceptanceCase extends BaseTestCase
{
    
    public static Process $process;
   
 
    public function get(string $path) {
        $curl = curl_init('http://127.0.0.1:8080' . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        $raw_response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        
        
        $headers = explode("\r\n", substr($raw_response, 0, $info['header_size']));
        $body = substr($raw_response, $info['header_size']);
        foreach ($headers as $header) {
            if (str_starts_with($header, 'Content-Type') && str_contains($header, 'json')) {
                $body = json_decode($body, true);
            }
        }
        
        return expect(new class($info['http_code'], $info['http_code'] === 200 ? $body : null) {
            public ?string $subject;
            public ?array $links;
            public ?array $aliases;
            public function __construct(public string $status, public mixed $body) {
                if (is_array($body)) {
                    $this->subject = $body['subject'] ?? null;
                    $this->links = $body['links'] ?? null;
                    $this->aliases = $body['aliases'] ?? null;
                }
            }
            
        });
    }
    
   
}