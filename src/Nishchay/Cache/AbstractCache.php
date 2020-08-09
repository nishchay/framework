<?php

namespace Nishchay\Cache;

/**
 * Abstract cache class for cache handler.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractCache
{

    public function __construct($servers)
    {
        $this->init($servers);
    }

    /**
     * 
     */
    abstract protected function init($servers);

    /**
     * Stores item only if it does not exist already.
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return boolean
     */
    abstract public function add($key, $value, $expiration): bool;

    /**
     * Replace item if it already exists.
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return mixed
     */
    abstract public function replace($key, $value, $expiration);

    /**
     * Stores an item.
     *  
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return mixed
     */
    abstract public function set($key, $value, $expiration);

    /**
     * Returns item by key.
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return mixed
     */
    abstract public function get($key);

    /**
     * Removes item
     * 
     * @param string $key
     * @return boolean
     */
    abstract public function remove($key): bool;
}
