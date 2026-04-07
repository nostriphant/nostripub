<?php


namespace nostriphant\nostripub;

/**
 *
 * @author meije005
 */
interface Endpoint {
    function __invoke(callable $respond);
}
