<?php declare(strict_types=1);
namespace RainCity;

use RainCity\Logging\Logger;
use RainCity\Logging\BaseLogger;


class DataCache
extends Singleton
implements \Psr\SimpleCache\CacheInterface
{
    /** @var \Apix\Cache\PsrCache\Pool | \Apix\Cache\PsrCache\TaggablePool */
    private $cache;

    /** @var int TTL in seconds */
    private $defaultTTL = 600;

    protected function __construct () {
        parent::__construct();

        // need to add support for Memcached
        $options = array();
        if (extension_loaded('memcached')) {

            $backend = new \Memcached();
            $backend->addServer('127.0.0.1', 11211);

            $this->cache = \Apix\Cache\Factory::getPool($backend, $options);
        }
        else if (extension_loaded('pdo_sqlite')) {

            $dbFile = self::getSqliteFile();
            $dbDir = dirname($dbFile);

            if (!file_exists($dbDir)) {
                mkdir($dbDir);
            }

            $backend = new \PDO('sqlite:'.self::getSqliteFile());
            $this->cache = \Apix\Cache\Factory::getPool($backend);
        }
        else {
            $options['directory'] = self::getFilesCacheDir(); // Directory where the cache is created
            $options['locking'] = true;                       // File locking (recommended)

            $this->cache = \Apix\Cache\Factory::getPool('Files', $options);
        }
    }

    public function deleteMultiple($keys)
    {
        return $this->cache->deleteItems($keys);
    }

    public function setMultiple($values, $ttl = null)
    {
        // Multiple values aren't being supported
    }

    public function getMultiple($keys, $default = null)
    {
        // Multiple values aren't being supported
    }

    public function set($key, $value, $ttl = null)
    {
        /** @var \Psr\Cache\CacheItemInterface */
        $item = $this->cache->getItem($key);

        $item->set($value);
        if (isset($ttl)) {
            $item->expiresAfter($ttl);
        }
        else {
            if (isset($this->defaultTTL)) {
                $ttl = $this->defaultTTL;
                $item->expiresAfter($ttl);
            }
        }

        return $this->cache->save($item);
    }

    public function get($key, $default = null)
    {
        $item = $this->cache->getItem($key);
        if (!isset($item) && isset($default)) {
            $this->log->debug("Request for $key not found, returning default");
            $item->set($default);
        }

        return $item->get();
    }

    public function clear()
    {
        return $this->cache->clear();
    }

    public function has($key)
    {
        return $this->cache->hasItem($key);
    }

    public function delete($key)
    {
        return $this->cache->deleteItem($key);
    }

    /**
     * Time-to-live, in seconds
     *
     * @param int $ttl
     */
    public function setDefaultTTL(int $ttl) {
        $this->defaultTTL = $ttl;
    }

    private static function getSqliteFile () {
        return sys_get_temp_dir() . '/datacache.sqlite3';
    }

    private static function getFilesCacheDir () {
        return sys_get_temp_dir() . '/files.cache';
    }

    public static function uninstall() {
        Logger::getLogger(BaseLogger::BASE_LOGGER)->info('DataCache::uninstall() called');

        @unlink(self::getSqliteFile());

        // delete all the Files cache files.
        array_map('unlink', array_filter((array) glob(self::getFilesCacheDir())));
        @rmdir(self::getFilesCacheDir());
    }
}
