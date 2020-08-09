<?php

namespace Nishchay\Cache\Handler;

use Nishchay\Exception\ApplicationException;
use Nishchay\Cache\AbstractCache;
use Redis;
use Nishchay\Utility\Coding;

/**
 * Redis cache handler.
 * 
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class RedisCache extends AbstractCache
{

    /**
     * Instance of Redis class.
     * 
     * @var Redis
     */
    private $redis;

    /**
     * Connects to redis server.
     * 
     * @param \stdClass $servers
     * @throws ApplicationException
     */
    protected function init($servers)
    {
        if (!isset($servers['host']) && !isset($servers['socket'])) {
            throw new ApplicationException('Invalid cache config host or'
                    . ' socket missing for redis.', null, null, 912001);
        }

        # Just preparing array and will pass to connect method using
        # spread operator.
        $params = [
            # Sure from above condition of these two will be there in config
            $servers['host'] ?? $servers['socket'],
            $servers['port'] ?? 6379,
            # Default timeout 1 second
            $servers['timeout'] ?? 1,
            $servers['reserved'] ?? null,
            # Default retry interval 100 miliseconds
            $servers['retryInterval'] ?? 100,
            # Default read interval 1 second
            $servers['readInterval'] ?? 1,
        ];

        $this->redis = new Redis();
        $this->redis->connect(...$params);

        # Authenticating to server if password exists in config
        if (isset($servers->password)) {
            $this->redis->auth($servers->password);
        }
    }

    /**
     * Serialize value to be stored in cache.
     * 
     * @param mixed $value
     * @return string
     */
    private function serialize($value)
    {
        return Coding::serialize(new RedisValue($value));
    }

    /**
     * 
     * @param string $value
     */
    private function unserialize($value)
    {
        if (Coding::isUnSerializable($value) === false) {
            return $value;
        }

        $value = Coding::unserialize($value);

        if ($value instanceof RedisValue) {
            return $value->getValue();
        }

        return $value;
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
        if ($this->redis->exists($key)) {
            return false;
        }

        return $this->set($key, $this->serialize($value), $expiration);
    }

    /**
     * Returns item by key.
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->unserialize($this->redis->get($key));
    }

    /**
     * Removes item
     * 
     * @param string $key
     * @return boolean
     */
    public function remove($key): bool
    {
        return $this->redis->del($key) ? true : false;
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
        return $this->redis->set($key, $this->serialize($value), ['xx', 'ex' => $expiration]);
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
        return $this->redis->set($key, $this->serialize($value), $expiration);
    }

    /**
     * Updates expiration time.
     * 
     * @param type $key
     * @param type $expiration
     */
    public function touch($key, $expiration)
    {
        return $this->redis->expire($key, $expiration);
    }

}
