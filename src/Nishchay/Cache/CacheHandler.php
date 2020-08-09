<?php

namespace Nishchay\Cache;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Cache\Handler\MemcachedCache;
use Nishchay\Cache\Handler\OfflineCache;
use Nishchay\Cache\Handler\RedisCache;
use Nishchay\Utility\Coding;

/**
 * Cache Handler
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class CacheHandler
{

    /**
     *
     * @var AbstractCache 
     */
    private $cache;

    /**
     * Flag for whether key need to be hashed.
     * 
     * @var boolean
     */
    private $isKeyToHash;

    /**
     * 
     * @param type $config
     */
    public function __construct($config)
    {
        $this->init($config);
    }

    /**
     * 
     * @param type $config
     * @throws ApplicationException
     * @throws NotSupportedException
     */
    protected function init($config)
    {
        if (!isset($config->type) || !isset($config->server)) {
            throw new ApplicationException('Invalid cache config.', null, null, 912002);
        }

        $this->setKeyToHash($config);

        $config->server = Coding::toArray($config->server);
        switch ($config->type) {
            case 'memcached':
                $this->cache = new MemcachedCache($config->server);
                break;
            case 'redis':
            case 'redisdb':
                $this->cache = new RedisCache($config->server);
                break;
            case 'offline':
                $this->cache = new OfflineCache($config->server);
                break;
            default :
                throw new NotSupportedException('Cache type [' .
                        $config->type . '] not supported.');
        }
    }

    private function setKeyToHash($config)
    {
        $this->isKeyToHash = (isset($config->hash) && is_bool($config->hash)) ? $config->hash : true;
    }

    /**
     * Stores item only if it does not exist already.
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return boolean
     */
    public function add($key, $value, $expiration)
    {
        return $this->cache->add($this->getHashed($key), $value, $expiration);
    }

    /**
     * Returns item by key.
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->cache->get($this->getHashed($key));
    }

    /**
     * Returns items of given keys in array.
     * 
     * @param array $keys
     * @return array
     */
    public function getMulti($keys)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key);
        }

        return $values;
    }

    /**
     * Removes item
     * 
     * @param string $key
     * @return boolean
     */
    public function remove($key)
    {
        return $this->cache->remove($this->getHashed($key));
    }

    /**
     * Remove multiple item and it returns count of item removed from cache.
     * 
     * @param array $keys
     * @return int
     */
    public function removeMulti($keys)
    {
        $removed = 0;
        foreach ($keys as $key) {
            $this->remove($key) && ($removed++);
        }

        return $removed;
    }

    /**
     * Replace item if it already exists.
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return mixed
     */
    public function replace($key, $value, $expiration)
    {
        return $this->cache->replace($this->getHashed($key), $value, $expiration);
    }

    /**
     * Stores an item.
     *  
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return mixed
     */
    public function set($key, $value, $expiration = 0)
    {
        return $this->cache->set($this->getHashed($key), $value, $expiration);
    }

    /**
     * Sets multiple item and returns number of item has been set successful.
     * 
     * @param array $array
     * @return int
     */
    public function setMulti(array $array): int
    {
        $count = 0;
        foreach ($array as $item) {
            $this->set(...$item) && ($count++);
        }

        return $count;
    }

    /**
     * Updates expiration time.
     * 
     * @param type $key
     * @param type $expiration
     */
    public function touch($key, $expiration)
    {
        return $this->cache->touch($this->getHashed($key), $expiration);
    }

    /**
     * Returns MD5 of key.
     * 
     * @param type $key
     * @return type
     */
    private function getHashed($key)
    {
        if ($this->isKeyToHash === false) {
            return $key;
        }

        return md5($key);
    }

}
