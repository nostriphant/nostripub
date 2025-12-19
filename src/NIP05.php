<?php

namespace nostriphant\nostripub;

use nostriphant\NIP01\Message;
use nostriphant\Client\Client;
use nostriphant\NIP19\Bech32;

final readonly class NIP05 {
    
    public function __construct(public Bech32 $pubkey, public array $relays) {
        
    }
    
    public static function lookup(array $discovery_relays, callable $http, callable $error) : callable {
        return function(string $nip05_identifier) use ($discovery_relays, $http, $error) : self {
            list($nostr_user, $nostr_domain) = explode('@', $nip05_identifier, 2);
            $json = $http('https://' . $nostr_domain . '/.well-known/nostr.json?name=' . $nostr_user, $error);
            if (isset($json['names'][$nostr_user]) === false) {
                exit($error());
            }
            return new self(Bech32::npub($json['names'][$nostr_user]), $json['relays'] ?? $discovery_relays);
        };
    }
    
    public function __invoke(callable $transform, callable $error): void {
        foreach ($this->relays as $discovery_relay) {
            $client = Client::connectToUrl($discovery_relay);
            error_log('Connecting to ' . $discovery_relay);

            $subscription_id = uniqid();

            $listen = $client(fn(\nostriphant\NIP01\Transmission $send) => $send(Message::req($subscription_id, ["kinds" => [0], "authors" => [($this->pubkey)()]])));

            $listen(function(Message $message, callable $stop) use ($transform, $subscription_id) {
                if ($message->payload[0] !== $subscription_id) {
                    return;
                } elseif ($message->type === 'EOSE') {
                    $stop();
                    return;
                }
                $stop();
                $transform(new \nostriphant\NIP01\Event(...$message->payload[1]));
            });
        }
        $error();
    }
}
