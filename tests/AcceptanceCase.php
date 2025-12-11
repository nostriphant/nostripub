<?php

namespace nostriphant\nostripubTests;

use PHPUnit\Framework\TestCase as BaseTestCase;


abstract class AcceptanceCase extends BaseTestCase
{
    
    private static Process $process;
    
    #[\Override]
    public static function setUpBeforeClass(): void {
        $cmd = [PHP_BINARY, '-S', '127.0.0.1:8080', '-d', 'variables_order=EGPCS', './public/router.php'];
        $env = [];
        self::$process = new Process('api', $cmd, $env, fn(string $line) => str_contains($line, 'Development Server (http://127.0.0.1:8080) started'));
    }
 
    public function get(string $path) {
        $curl = curl_init('http://127.0.0.1:8080' . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        
        return expect(new class($info['http_code'], $info['http_code'] === 200 ? json_decode($body, true) : null) {
            public ?string $subject;
            public ?array $links;
            public function __construct(public string $status, ?array $content) {
                $this->subject = $content['subject'] ?? null;
                $this->links = $content['links'] ?? null;
            }
        });
    }
    
    
    #[\Override]
    public static function tearDownAfterClass(): void {
        call_user_func(self::$process);
    }
}