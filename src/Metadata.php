<?php
namespace nostriphant\nostripub;
use nostriphant\NIP01\Message;
use nostriphant\Client\Client;

readonly class Metadata {
    
    public function __construct(private ?\nostriphant\NIP01\Event $event) {
        ;
    }
    
    public function __invoke(callable $transform): mixed {
        return $transform($this->event);
    }
    
    
    public static function discoverByNpub(NIP05 $nip05, callable $transform, callable $error): void {
        // we need to discover where this npub is posting
        
        foreach ($nip05->relays as $discovery_relay) {
            $client = Client::connectToUrl($discovery_relay);
            error_log('Connecting to ' . $discovery_relay);

            $subscription_id = uniqid();

            $listen = $client(fn(\nostriphant\NIP01\Transmission $send) => $send(Message::req($subscription_id, ["kinds" => [0], "authors" => [($npub)()]])));

            $listen(function(\nostriphant\NIP01\Message $message, callable $stop) use ($transform, $subscription_id) {
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
