<?php
namespace nostriphant\nostripub\WebfingerResource;

use nostriphant\nostripub\HTTP;
use nostriphant\nostripub\HTTPStatus;
use nostriphant\nostripub\Respond;

readonly class Acct {
    public function __construct(private string $baseurl, private \nostriphant\nostripub\KeyRepository $keys) {
    }
    
    public function __invoke(string $handle, HTTP $http, Respond $respond): void {
        list($user, $domain) = explode('@', $handle, 2);
        error_log('Matching ' . $domain . ' to '. parse_url($baseurl, PHP_URL_HOST));
        if ($domain !== parse_url($this->baseurl, PHP_URL_HOST)) {
            $respond(HTTPStatus::_302, ['Location: https://' . $domain . '/.well-known/webfinger?resource=acct:' . urlencode($handle)]);
        }

        $subhandle = str_replace('.at.', '@', $user);
        list($user, $domain) = explode('@', $subhandle, 2);
        $activity_pub_account = $http('https://' . $domain . '/.well-known/webfinger?resource=acct:' . urlencode($subhandle), $respond);

        $keys = ($this->keys)($subhandle);
        $pubkey = $keys['public_key'];

        $activity_pub_account['subject'] = 'acct:' . $handle;
        $activity_pub_account['aliases'][] = $this->baseurl . '/@'.$pubkey;

        $respond(headers:['Content-Type: application/jrd+json'], body:json_encode($activity_pub_account));;
    }
}
