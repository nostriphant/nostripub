<?php
namespace nostriphant\nostripub\WebfingerResource;

use nostriphant\nostripub\HTTP;
use nostriphant\nostripub\HTTPStatus;
use nostriphant\nostripub\Respond;

readonly class Acct {
    public function __construct(private string $handle, private \nostriphant\nostripub\KeyRepository $keys) {
    }
    
    public function __invoke(string $baseurl, HTTP $http, Respond $respond): void {
        list($user, $domain) = explode('@', $this->handle, 2);
        error_log('Matching ' . $domain . ' to '. parse_url($baseurl, PHP_URL_HOST));
        if ($domain !== parse_url($baseurl, PHP_URL_HOST)) {
            $respond(HTTPStatus::_302, ['Location: https://' . $domain . '/.well-known/webfinger?resource=acct:' . urlencode($this->handle)]);
        }

        $handle = str_replace('.at.', '@', $user);
        list($user, $domain) = explode('@', $handle, 2);
        $activity_pub_account = $http('https://' . $domain . '/.well-known/webfinger?resource=acct:' . urlencode($handle), $respond);

        $keys = ($this->keys)($handle);
        $pubkey = $keys['public_key'];

        $activity_pub_account['subject'] = 'acct:' . $this->handle;
        $activity_pub_account['aliases'][] = $baseurl . '/@'.$pubkey;

        $respond(headers:['Content-Type: application/jrd+json'], body:json_encode($activity_pub_account));;
    }
}
