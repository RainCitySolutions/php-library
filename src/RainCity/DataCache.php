<?php declare(strict_types=1);
namespace RainCity;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use RainCity\Logging\BaseLogger;
use RainCity\Logging\Logger;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Traversable;

class DataCache extends Singleton implements CacheInterface
{
    /** @var CacheItemPoolInterface */
    private $cache;

    /** @var int TTL in seconds */
    private $defaultTTL = 600;

    protected function __construct($args)
    {
        parent::__construct();

        if (is_array($args) && !empty($args) && $args[0] instanceof CacheItemPoolInterface) {
            $this->cache = $args[0];
        } else {
            // need to add support for Memcached
            if (extension_loaded('memcached')) {

                $client = MemcachedAdapter::createConnection('memcached://localhost:11211');

                $this->cache = new MemcachedAdapter($client);
            } elseif (extension_loaded('pdo_sqlite')) {
                $dbFile = self::getSqliteFile();
                $dbDir = dirname($dbFile);

                if (!file_exists($dbDir)) {
                    mkdir($dbDir);
                }

                $backend = new \PDO('sqlite:'.self::getSqliteFile());
                $this->cache = new PdoAdapter($backend);
            } else {
                $this->cache = new FilesystemAdapter('', 0, self::getFilesCacheDir());
            }
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \Psr\SimpleCache\CacheInterface::set()
     */
    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        /** @var \Psr\Cache\CacheItemInterface */
        $item = $this->cache->getItem($key);

        $item->set($value);
        if (isset($ttl)) {
            $item->expiresAfter($ttl);
        } else {
            if (isset($this->defaultTTL)) {
                $ttl = $this->defaultTTL;
                $item->expiresAfter($ttl);
            }
        }

        return $this->cache->save($item);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Psr\SimpleCache\CacheInterface::get()
     */
    public function get(string $key, mixed $default = null): mixed
    {
        /** @var \Psr\Cache\CacheItemInterface */
        $item = $this->cache->getItem($key);
        if (isset($item) && !$item->isHit() && isset($default)) {
            $this->log->debug("Request for $key not found, returning default");
            $item->set($default);
            $this->cache->save($item);
        }

        return $item->get();
    }

    /**
     *
     * {@inheritDoc}
     * @see \Psr\SimpleCache\CacheInterface::clear()
     */
    public function clear(): bool
    {
        return $this->cache->clear();
    }

    /**
     *
     * {@inheritDoc}
     * @see \Psr\SimpleCache\CacheInterface::has()
     */
    public function has(string $key): bool
    {
        return $this->cache->hasItem($key);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Psr\SimpleCache\CacheInterface::delete()
     */
    public function delete(string $key): bool
    {
        return $this->cache->deleteItem($key);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Psr\SimpleCache\CacheInterface::deleteMultiple()
     */
    public function deleteMultiple(Traversable|array $keys): bool
    {
        return $this->cache->deleteItems($keys);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Psr\SimpleCache\CacheInterface::setMultiple()
     */
    public function setMultiple(Traversable|array $values, \DateInterval|int|null $ttl = null): bool
    {
        $result = false;

        if (!is_array($values) && !($values instanceof Traversable)) {
            throw new DataCacheException('values argument must be an array or implement the Transversable interface');
        }

        foreach ($values as $key => $value) {
            if (!is_string($key)) {
                throw new DataCacheException('Invalid key included in values parameter');
            }

            $result = $this->set($key, $value, $ttl) || $result;
        }

        return $result;
    }

    /**
     *
     * {@inheritDoc}
     * @see \Psr\SimpleCache\CacheInterface::getMultiple()
     */
    public function getMultiple(Traversable|array $keys, mixed $default = null): Traversable|array
    {
        $result = array();

        if (!is_array($keys) && !($keys instanceof Traversable)) {
            throw new DataCacheException('keys argument must be an array or implement the Transversable interface');
        }

        foreach ($keys as $key) {
            if (!is_string($key)) {
                throw new DataCacheException('key argument must be a string or an integer');
            }

            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * Time-to-live, in seconds
     *
     * @param int $ttl
     */
    public function setDefaultTTL(int $ttl)
    {
        $this->defaultTTL = $ttl;
    }

    private static function getSqliteFile()
    {
        return sys_get_temp_dir() . '/datacache.sqlite3';
    }

    private static function getFilesCacheDir()
    {
        return sys_get_temp_dir() . '/files.cache';
    }

    public static function uninstall()
    {
        Logger::getLogger(BaseLogger::BASE_LOGGER)->info('DataCache::uninstall() called');

        @unlink(self::getSqliteFile());

        // delete all the Files cache files.
        array_map('unlink', array_filter((array) glob(self::getFilesCacheDir())));
        @rmdir(self::getFilesCacheDir());
    }
}
