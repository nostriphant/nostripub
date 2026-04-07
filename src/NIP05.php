<?php

namespace nostriphant\nostripub;

use nostriphant\NIP01\Message;
use nostriphant\Client\Client;
use nostriphant\NIP19\Bech32;

final readonly class NIP05 {
    
    
    public function __construct(public Bech32 $pubkey, public array $relays, private Respond $error) {
        
    }
    
    public static function lookup(array $discovery_relays, callable $http, Respond $error) : callable {
        return function(string $nip05_identifier) use ($discovery_relays, $http, $error) : self {
            if (str_contains($nip05_identifier, '@')) {
                list($nostr_user, $nostr_domain) = explode('@', $nip05_identifier, 2);
                $json = $http('https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user, $error);
                if (isset($json['names'][$nostr_user]) === false) {
                    $error(HTTPStatus::_404);
                }
                return new self(Bech32::npub($json['names'][$nostr_user]), $json['relays'] ?? $discovery_relays, $error);
            } elseif (str_starts_with($nip05_identifier, 'npub1')) {
                return new self(new \nostriphant\NIP19\Bech32($nip05_identifier), $discovery_relays, $error);
            }
            
            $error(HTTPStatus::_422);
        };
    }
    
    public function __invoke(callable $transform): void {
        foreach ($this->relays as $discovery_relay) {
            try {
                error_log('Connecting to ' . $discovery_relay);
                $client = Client::connectToUrl($discovery_relay);
            } catch (Amp\Websocket\Client\WebsocketConnectException $e) {
                ($this->error)(HTTPStatus::_500);
            }

            $subscription_id = uniqid();

            $listen = $client(fn(\nostriphant\NIP01\Transmission $send) => $send(Message::req($subscription_id, ["kinds" => [0], "authors" => [($this->pubkey)()]])));

            $listen(function(Message $message, callable $stop) use ($transform, $subscription_id) {
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
                $transform(new \nostriphant\NIP01\Event(...$message->payload[1]));
            });
        }
        ($this->error)(HTTPStatus::_422);
    }
}
