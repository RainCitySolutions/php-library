<?php declare(strict_types=1);
namespace RainCity\Json;

use JsonMapper\JsonMapperBuilder;
use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use JsonMapper\Handler\FactoryRegistry;
use JsonMapper\Handler\PropertyMapper;
use JsonMapper\Middleware\Rename\Rename;
use Psr\SimpleCache\CacheInterface;
use RainCity\DataCache;

/**
 * The JsonClientTrait provides a means for clients working with a JSON API
 * and utilizing the JsonEntity class to convert JSON responses into class
 * instances.
 *
 * It also provides a means to cache the JSON responses to avoid duplicate
 * requests to the server.
 */
trait JsonClientTrait
{
    protected CacheInterface $cache;
    protected JsonMapperInterface $mapper;

    /**
     * Initialize the cache TTL and the mapper.
     *
     * @param int $cacheTTL The Time-To-Live for cache entries, in seconds.
     *      Defaults to 10 seconds.
     * @param FactoryRegistry $factoryRegistry A factory to use in mapping
     *      properties. If not provide no special factory registry will be
     *      used.
     */
    final protected function initJsonClientTrait(int $cacheTTL = 10, FactoryRegistry $factoryRegistry = null)
    {
        $this->cache = DataCache::instance();
        $this->cache->setDefaultTTL($cacheTTL);

        // Create our own builder so we can include a PropertyMapper with additional class factories
        $builder = JsonMapperBuilder::new();

        if (isset($factoryRegistry)) {
            $builder->withPropertyMapper(new PropertyMapper($factoryRegistry));
        }

        $this->mapper = (new JsonMapperFactory($builder))->bestFit();
        $this->mapper->push(new \JsonMapper\Middleware\CaseConversion(
            \JsonMapper\Enums\TextNotation::UNDERSCORE(),
            \JsonMapper\Enums\TextNotation::CAMEL_CASE()
            ));
    }

    /**
     * Create a key for use with with the cache which is unique to the class
     * using JsonClientTrait.
     *
     * The method does not check that the generated key is unique within the
     * cache.
     *
     * @param  string ...$keyParams A set of strings to be used to create a
     *      unique key.
     *
     * @return string The generated cache key.
     */
    final protected function getCacheKey(string ...$keyParams)
    {
        // Because we are a trait, __CLASS__ will be the class using the trait.
        $nsParts = explode('\\', __CLASS__);

        // Use the class name without the namespace
        return end($nsParts).'_'.join('_', $keyParams);
    }

    /**
     * Process a Json object (as a string), converting it to an entity or
     * array of entities.
     *
     * @param string $jsonPayload The Json string
     * @param JsonEntity $entityObj An instance of a class extending
     *      JsonEntity
     * @param string $cacheKey The key to use for caching the result. Pass
     *      null to avoid caching the result.
     *
     * @return object|array|NULL An entity instance, array of instances or
     *      null if the Json cannot be converted.
     */
    final protected function processJsonResponse(
        string $jsonPayload,
        JsonEntity $entityObj,
        ?string $cacheKey = null
        )
    {
        $result = null;

        /** @var Rename */
        $rename = $entityObj->getRenameMapping();

        $this->mapper->unshift($rename);

        try {
            $json = json_decode($jsonPayload);

            if (isset($json)) {
                if (is_array($json)) {
                    // Convert array of arrays to array of stdClass
                    $json = array_map(fn($entry) => (object)$entry, $json);

                    $result = $this->mapper->mapArray($json, $entityObj);
                } else {
                    $result = $this->mapper->mapObject($json, $entityObj);
                }

                if (null != $cacheKey) {
                    $this->cache->set($cacheKey, $result);
                }
            }
        } finally {
            $this->mapper->remove($rename);
        }

        return $result;
    }
}
