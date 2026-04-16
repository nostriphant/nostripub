<?php

$process;
beforeAll(function() use (&$process) {
        $cmd = [PHP_BINARY, '-S', '127.0.0.1:8080', '-d', 'variables_order=EGPCS', './router.php'];
        $env = [
            'DISCOVERY_RELAY0' => 'relay.mostr.pub',
            'DISCOVERY_RELAY1' => 'relay.noswhere.com',
            'DISCOVERY_RELAY2' => 'purplepag.es'
        ];
        
        
        $process = new \nostriphant\nostripubTests\Process('api', $cmd, $env, fn(string $line) => str_contains($line, 'Development Server (http://127.0.0.1:8080) started'));
        sleep(1);
});

describe('root', function() {
    it('responds with a 404 status code')
            ->get('/')
            ->status->toBe('404');
});

afterAll(function() use (&$process)  {
    $process();
});
