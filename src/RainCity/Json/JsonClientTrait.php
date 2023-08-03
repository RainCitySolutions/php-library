<?php
namespace RainCity\Json;

use JsonMapper\JsonMapperInterface;
use JsonMapper\Middleware\Rename\Rename;
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
    protected DataCache $cache;
    protected JsonMapperInterface $mapper;

    /**
     * Constructor which can be overwritten by child classes. They should
     * still call this constructor so that logging is initialized.
     *
     * Care should be taken in the constructor to avoid doing anything that
     * might call instance() on the singleton as this will lead to a
     * recursive loop. It is preferred to do any initialization of the
     * instance in the initializeInstance() method.
     */
    final protected function __construct(int $cacheTTL = 10)
    {
        $this->cache = DataCache::instance();
        $this->cache->setDefaultTTL($cacheTTL);
    }

    final protected function getCacheKey(string $method, ...$params)
    {
        $key = str_replace(__NAMESPACE__ . '\\', '', __CLASS__).'_'.$method;
        
        if (!empty($params)) {
            $key = $key.'_'.join('_', $params);
        }
        
        return $key;
    }

    final protected function processJsonResponse(
        string $jsonPayload,
        JsonEntity $entityObj,
//        Rename $rename,
        ?string $cacheKey = null
        ): ?array
    {
        $jsonArray = json_decode($jsonPayload);
        
        // Convert array of arrays to array of stdClass
        $json = array_map(fn($entry) => (object)$entry, $jsonArray);

        /** @var Rename */
        $rename = $entityObj->getRenameMapping();
        
        $this->mapper->unshift($rename);
        
        $entityArray = $this->mapper->mapArray($json, $entityObj);
        
        $this->mapper->remove($rename);
        
        if (null != $cacheKey) {
            $this->cache->set($cacheKey, $entityArray);
        }
        
        return $entityArray;
    }
}
