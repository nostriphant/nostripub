<?php

namespace nostriphant\nostripub;

use nostriphant\NIP01\Message;
use nostriphant\Client\Client;
use nostriphant\NIP19\Bech32;

final readonly class NIP05 {
    
    
    public function __construct(public Bech32 $pubkey, public array $relays, private Respond $respond) {
        
    }
    
    public static function lookup(HTTP $http) : callable {
        $discovery_relays = array_map(fn(string $relay) => 'wss://'. $relay, array_filter($_ENV, fn(string $key) => str_starts_with($key, 'DISCOVERY_RELAY'), ARRAY_FILTER_USE_KEY));
        return function(string $nip05_identifier, Respond $respond) use ($discovery_relays, $http) : self {
            if (str_contains($nip05_identifier, '@')) {
                list($nostr_user, $nostr_domain) = explode('@', $nip05_identifier, 2);
                $json = $http('https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user, $respond);
                if (isset($json['names'][$nostr_user]) === false) {
                    $respond(HTTPStatus::_404);
                }
                return new self(Bech32::npub($json['names'][$nostr_user]), $json['relays'] ?? $discovery_relays, $respond);
            } elseif (str_starts_with($nip05_identifier, 'npub1')) {
                return new self(new \nostriphant\NIP19\Bech32($nip05_identifier), $discovery_relays, $respond);
            }
            
            $respond(HTTPStatus::_422);
        };
    }
    
    public function __invoke(callable $transform): void {
        $found = false;
        foreach ($this->relays as $discovery_relay) {
            if ($found) {
                break;
            }
            try {
                error_log('Connecting to ' . $discovery_relay);
                $client = Client::connectToUrl($discovery_relay);
            } catch (Amp\Websocket\Client\WebsocketConnectException $e) {
                ($this->respond)(HTTPStatus::_500);
                return;
            }

            $subscription_id = uniqid();

            $listen = $client(fn(\nostriphant\NIP01\Transmission $send) => $send(Message::req($subscription_id, ["kinds" => [0], "authors" => [($this->pubkey)()]])));

            $listen(function(Message $message, callable $stop) use ($transform, $subscription_id, &$found) {
                if ($found) {
                    return;
                }
                if ($message->payload[0] !== $subscription_id) {
                    return;
                } elseif ($message->type === 'EOSE') {
                    $stop();
                    return;
                } elseif ($message->type === 'CLOSED') {
                    error_log('Subscription closed: ' . $message->payload[1]);
                    $stop();
                    return;
                }
                $stop();
                $found = true;
                $transform(new \nostriphant\NIP01\Event(...$message->payload[1]));
            });
        }
        if ($found === false) {
            ($this->respond)(HTTPStatus::_422);
        }
    }
}
