<?php
namespace nostriphant\nostripub\WebfingerResource;

use nostriphant\nostripub\HTTP;
use nostriphant\nostripub\HTTPStatus;
use nostriphant\nostripub\Respond;

readonly class Acct implements \nostriphant\nostripub\WebfingerResource {
    public function __construct(private string $baseurl, private \nostriphant\nostripub\KeyRepository $keys, private HTTP $http, private Respond $respond) {
    }
    
    public function __invoke(string $handle): void {
        list($user, $domain) = explode('@', $handle, 2);
        error_log('Matching ' . $domain . ' to '. parse_url($baseurl, PHP_URL_HOST));
        if ($domain !== parse_url($this->baseurl, PHP_URL_HOST)) {
            ($this->respond)(HTTPStatus::_302, ['Location: https://' . $domain . '/.well-known/webfinger?resource=acct:' . urlencode($handle)]);
        }

        $subhandle = str_replace('.at.', '@', $user);
        list($user, $domain) = explode('@', $subhandle, 2);
        $activity_pub_account = ($this->http)('https://' . $domain . '/.well-known/webfinger?resource=acct:' . urlencode($subhandle), $this->respond);

        $keys = ($this->keys)($subhandle);
        $pubkey = $keys['public_key'];

        $activity_pub_account['subject'] = 'acct:' . $handle;
        $activity_pub_account['aliases'][] = $this->baseurl . '/@'.$pubkey;

        ($this->respond)(headers:['Content-Type: application/jrd+json'], body:json_encode($activity_pub_account));;
    }
}
