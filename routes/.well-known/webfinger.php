<?php


return new class implements nostriphant\nostripub\Endpoint {
    #[\Override]
    public function __invoke(nostriphant\nostripub\Respond $respond) {
        if (isset($_GET['resource']) === false) {
            $respond(\nostriphant\nostripub\HTTPStatus::_400);
            return;
        }
        $http = new \nostriphant\nostripub\HTTP(CACHE_DIR);
                
        $browser_scheme = 'http'. ($_SERVER['HTTPS'] ?? 'off' !== 'off' ? 's' : '');
        $browser_hostname = $_SERVER["HTTP_HOST"];
        $baseurl = $browser_scheme . '://' . $browser_hostname;
        $webfinger = new nostriphant\nostripub\Webfinger($baseurl, $http, $respond);
        
        list($scheme, $handle) = explode(':', $_GET['resource'], 2);
        $resource = $webfinger($scheme);
        $resource($handle);

    }
};
