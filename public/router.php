<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use nostriphant\NIP01\Message;
use nostriphant\Client\Client;

$browser_hostname = $_SERVER["HTTP_HOST"];
$browser_scheme = 'http'. ($_SERVER['HTTPS'] ?? 'off' !== 'off' ? 's' : '');
$discovery_relays = array_map(fn(string $relay) => 'wss://'. $relay,[
    'indexer.coracle.social',
    'relay.nostr.band',
    'relay.mostr.pub',
    'relay.noswhere.com'
]);

if (isset($_GET['resource']) === false) {
    header('HTTP/1.1 400 Bad Request', true);
    exit('Bad Request');
    
}
$requested_resource = $_GET['resource'];

list($scheme, $handle) = explode(':', $requested_resource, 2);

if ($scheme === 'acct') {
    list($user, $domain) = explode('@', $handle, 2);
    header('HTTP/1.1 302 Found', true);
    header('Location: https://' . $domain . '/.well-known/webfinger?resource=' . urlencode($requested_resource));
    exit('Found');
}

if (str_contains($handle, '@') === false) {
    try {
        $bech32 = new nostriphant\NIP19\Bech32($handle);
    } catch (Exception $e) {
        header('HTTP/1.1 422 Unprocessable Content', true);
        exit('Unprocessable Content');
    }
    
    $relays = [];
} else {
    list($nostr_user, $nostr_domain) = explode('@', $handle, 2);
    $curl = curl_init('https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $body = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);
    if ($info['http_code'] !== 200) {
        header('HTTP/1.1 404 Not found', true);
        exit('Not found');
    }

    $json = json_decode($body, true);
    if (isset($json['names'][$nostr_user]) === false) {
        header('HTTP/1.1 404 Not found', true);
        exit('Not found');
    }

    try {
        $bech32 = nostriphant\NIP19\Bech32::npub($json['names'][$nostr_user]);
    } catch (Exception $e) {
        header('HTTP/1.1 422 Unprocessable Content', true);
        exit('Unprocessable Content');
    }
    
    $relays = $json['relays'] ?? [];
}

    
switch ($bech32->type) {
    case 'npub':
        if (count($relays) === 0) {
            // we need to discover where this npub is posting
            $metadata;
            foreach ($discovery_relays as $discovery_relay) {
                $client = Client::connectToUrl($discovery_relay);
                error_log('Connecting to ' . $discovery_relay);

                $subscription_id = uniqid();

                $listen = $client(function(\nostriphant\NIP01\Transmission $send) use ($bech32, $discovery_relay, $subscription_id) {
                    $message = Message::req($subscription_id, ["kinds" => [0], "authors" => [$bech32()]]);
                    error_log('request to '. $discovery_relay.': '. $message);
                    $send($message);
                });

                $listen(function(\nostriphant\NIP01\Message $message, callable $stop) use (&$metadata, $discovery_relay, $subscription_id) {
                    // code to handle incoming messages
                    error_log('response from '. $discovery_relay.': '. $message);
                    if ($message->payload[0] !== $subscription_id) {
                        return;
                    } elseif ($message->type === 'EOSE') {
                        $stop(); // stops listening
                        return;
                    }

                    $metadata = new \nostriphant\NIP01\Event(...$message->payload[1]);
                    $stop(); // stops listening
                });

                if (isset($metadata)) {
                    break;
                }
            }
        }

        $links = [[
                "rel" => "http://webfinger.net/rel/profile-page",
                "href" => $browser_scheme.'://'.$browser_hostname.'/@'.$bech32
        ]];
        $avatar = '';
        if (\nostriphant\NIP01\Event::hasTag($metadata, "picture")) {
            $avatar_url = \nostriphant\NIP01\Event::extractTagValues($metadata, "picture")[0][0];
            $curl = curl_init($avatar_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);

            $links[] = [
                "rel" => "http://webfinger.net/rel/avatar",
                "type" => $info['content_type'],
                "href" => $info['url']
            ];
        }
        //$public_key_hex = $bech32();
        //$public_key_bech32 = (string) (Bech32::npub($public_key_hex));
        header('Content-Type: application/jrd+json', true);
        print '{
                    "subject": "'.$requested_resource.'",
                    "aliases": [],
                    "properties": {},
                    "links": '. json_encode($links). '

            }';

        break;

    default:
        header('HTTP/1.1 422 Unprocessable Content', true);
        exit('Unprocessable Content');
}