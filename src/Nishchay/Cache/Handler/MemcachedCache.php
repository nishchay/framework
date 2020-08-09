<?php

namespace Nishchay\Cache\Handler;

use Memcached;
use Nishchay\Cache\AbstractCache;

/**
 * Memcached cache handler.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class MemcachedCache extends AbstractCache
{

    /**
     *
     * @var Memcached
     */
    private $memcached;

    /**
     * 
     * @param type $servers
     */
    public function __construct($servers)
    {
        parent::__construct($servers);
    }

    /**
     * Adds servers.
     * 
     * @param array $servers
     */
    protected function init($servers)
    {
        $this->memcached = new Memcached();
        if (isset($servers['host'])) {
            $servers = [$servers];
        }

        foreach ($servers as $key => $value) {
            $servers[$key] = array_values($value);
        }

        $this->memcached->addServers($servers);
    }

    /**
     * Stores item only if it does not exist already.
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return boolean
     */
    public function add($key, $value, $expiration): bool
    {
        return $this->memcached->add($key, $value, $expiration);
    }

    /**
     * Returns item by key.
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->memcached->get($key);
    }

    /**
     * Removes item
     * 
     * @param string $key
     * @return boolean
     */
    public function remove($key): bool
    {
        return $this->memcached->delete($key);
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
        return $this->memcached->replace($key, $value, $expiration);
    }

    /**
     * Stores an item.
     *  
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return mixed
     */
    public function set($key, $value, $expiration)
    {
        return $this->memcached->set($key, $value, $expiration);
    }

    /**
     * Updates expiration time.
     * 
     * @param type $key
     * @param type $expiration
     */
    public function touch($key, $expiration)
    {
        return $this->memcached->touch($key, $expiration);
    }

}
