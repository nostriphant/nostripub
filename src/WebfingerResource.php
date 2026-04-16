<?php

namespace nostriphant\nostripub;

interface WebfingerResource {
    function __invoke(string $handle): void;
}
