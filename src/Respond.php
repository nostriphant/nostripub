<?php


namespace nostriphant\nostripub;

class Respond {
    public function __invoke(\nostriphant\nostripub\HTTPStatus $status = \nostriphant\nostripub\HTTPStatus::_200, array $headers = [], ?string $body = null) : void {
        header('HTTP/1.1 '.substr($status->name, 1).' ' . $status->value, true);
        foreach ($headers as $header) {
            header($header, true);
        }
        exit($body ?? $status->value);
    }
}
