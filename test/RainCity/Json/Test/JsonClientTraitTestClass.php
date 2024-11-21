<?php
declare(strict_types=1);
namespace RainCity\Json\Test;

use JsonMapper\Handler\FactoryRegistry;
use RainCity\Json\JsonClientTrait;

/**
 * This class is in a seperate namespace that JsonClientTrait to ensure that
 * the testing of getCacheKey() can exercise the container class being in a
 * different namespace.
 */
class JsonClientTraitTestClass
{
    use JsonClientTrait;

    public function __construct(?int $ttl = null, ?FactoryRegistry $factory = null)
    {
        if (isset($ttl)) {
            if (isset($factory)) {
                $this->initJsonClientTrait($ttl, $factory);
            } else {
                $this->initJsonClientTrait($ttl);
            }
        } else {
            $this->initJsonClientTrait();
        }
    }
}
