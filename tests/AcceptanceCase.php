<?php

namespace nostriphant\nostripubTests;

use PHPUnit\Framework\TestCase as BaseTestCase;


abstract class AcceptanceCase extends BaseTestCase
{
    
    private static Process $process;
    
    #[\Override]
    public static function setUpBeforeClass(): void {
        $cmd = [PHP_BINARY, '-S', '127.0.0.1:8080', '-d', 'variables_order=EGPCS', './public/router.php'];
        $env = ['NOSTRIPUB_DOMAIN' => 'nostripub.tld'];
        self::$process = new Process('api', $cmd, $env, fn(string $line) => str_contains($line, 'Development Server (http://127.0.0.1:8080) started'));
    }
 
    public function get(string $path) {
        $curl = curl_init('http://127.0.0.1:8080' . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        
        return new class($info['http_code'], $body) {
            public function __construct(private string $status, private string $content) {
            }
            public function assertStatus(string $expected_status) : void {
                expect($this->status)->toBe($expected_status);
            }
        };
    }
    
    
    #[\Override]
    public static function tearDownAfterClass(): void {
        call_user_func(self::$process);
    }
}
