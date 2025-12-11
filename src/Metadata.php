<?php
namespace nostriphant\nostripub;
use nostriphant\NIP01\Message;
use nostriphant\Client\Client;

readonly class Metadata {
    
    public static function discoverByNpub(\nostriphant\NIP19\Bech32 $npub, array $discovery_relays): \nostriphant\NIP01\Event {
        // we need to discover where this npub is posting
        $metadata;
        foreach ($discovery_relays as $discovery_relay) {
            $client = Client::connectToUrl($discovery_relay);
            error_log('Connecting to ' . $discovery_relay);

            $subscription_id = uniqid();

            $listen = $client(fn(\nostriphant\NIP01\Transmission $send) => $send(Message::req($subscription_id, ["kinds" => [0], "authors" => [($npub)()]])));

            $listen(function(\nostriphant\NIP01\Message $message, callable $stop) use (&$metadata, $discovery_relay, $subscription_id) {
                // code to handle incoming messages
                error_log('response from '. $discovery_relay.': '. $message);
                if ($message->payload[0] !== $subscription_id) {
                    return;
                } elseif ($message->type === 'EOSE') {
                    $stop();
                    return;
                }

                $metadata = new \nostriphant\NIP01\Event(...$message->payload[1]);
                $stop();
            });

            if (isset($metadata)) {
                return $metadata;
            }
        }
    }
}
