<?php

namespace Nishchay\Cache\Handler;

use Nishchay\Cache\AbstractCache;

/**
 * This is just a class which returns false for every method.
 * Used for offline cache.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class OfflineCache extends AbstractCache
{

    /**
     * @param array $servers
     * @return booleam
     */
    protected function init($servers)
    {
        return false;
    }

    /**
     * @param stirng $key
     * @param mixed $value
     * @param int $expiration
     * @return booleam
     */
    public function add($key, $value, $expiration): bool
    {
        return false;
    }

    /**
     * @param stirng $key
     * @return booleam
     */
    public function get($key)
    {
        return null;
    }

    /**
     * @param stirng $key
     * @return booleam
     */
    public function remove($key): bool
    {
        return false;
    }

    /**
     * @param stirng $key
     * @param mixed $value
     * @param int $expiration
     * @return booleam
     */
    public function replace($key, $value, $expiration)
    {
        return false;
    }

    /**
     * 
     * @param stirng $key
     * @param mixed $value
     * @param int $expiration
     * @return booleam
     */
    public function set($key, $value, $expiration)
    {
        return false;
    }

    /**
     * 
     * @param stirng $key
     * @param int $expiration
     * @return booleam
     */
    public function touch($key, $expiration)
    {
        return false;
    }

}
