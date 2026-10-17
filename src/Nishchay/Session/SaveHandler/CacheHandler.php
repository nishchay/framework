<?php

namespace Nishchay\Session\SaveHandler;

use Nishchay;
use Nishchay\Exception\ApplicationException;

/**
 * Session save handler type cache.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class CacheHandler extends AbstractSaveHandler
{

    /**
     * Cache config name.
     * 
     * @var string 
     */
    private $name;

    /**
     * Expiry time of cache.
     * @var int
     */
    private $expiryTime;

    /**
     * 
     */
    public function __construct($conig)
    {
        if (property_exists($conig, 'name') === false || isset($conig->expiry) === false) {
            throw new ApplicationException('Invalid cache config for session.', null, null, 929001);
        }
        $this->name = $conig->name;
        $this->expiryTime = (int) $conig->expiry;

        if ($this->expiryTime < 300) {
            throw new ApplicationException('Session cache expiry time should'
                    . ' be greater than 300 seconds.', null, null, 929002);
        }
    }

    /**
     * Just returns true.
     * 
     * @return type
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Removes session data from cache.
     * 
     * @param type $sessionId
     * @return type
     */
    public function destroy(string $sessionId): bool
    {
        return (bool) Nishchay::getCache($this->name)->remove('ses_' . $sessionId);
    }

    /**
     * 
     * @param type $maxlifetime
     */
    public function gc(int $maxlifetime): int|false
    {
        return 0;
    }

    /**
     * Just returns TRUE as open cache is not required.
     * 
     * @param type $save_path
     * @param type $session_name
     */
    public function open(string $save_path, string $session_name): bool
    {
        return true;
    }

    /**
     * Reads session data from cache.
     * 
     * @param type $sessionId
     */
    public function read(string $sessionId): string|false
    {
        $data = Nishchay::getCache($this->name)->get('ses_' . $sessionId);

        # Refreshing caching expiry time.
        if (!empty($data)) {
            Nishchay::getCache($this->name)->touch('ses_' . $sessionId, $this->expiryTime);
        }

        return is_string($data) ? $data : '';
    }

    /**
     * Write session data to cache.
     * 
     * @param string $sessionId
     * @param string $data
     * @return boolean
     */
    public function write(string $sessionId, string $data): bool
    {
        return (bool) Nishchay::getCache($this->name)->set('ses_' . $sessionId, $data, $this->expiryTime);
    }

}
