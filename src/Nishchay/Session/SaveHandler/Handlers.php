<?php

namespace Nishchay\Session\SaveHandler;

use Nishchay;
use Nishchay\Exception\ApplicationException;

/**
 * Processing of the application
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Handlers
{

    /**
     * Type of session storage.
     * 
     * @var string
     */
    private $storage;

    /**
     *
     * @var \SessionHandlerInterface 
     */
    private $handler;

    /**
     * 
     * @param type $storage
     */
    public function __construct($storage)
    {
        $this->storage = $storage;
    }

    /**
     * 
     * @return \SessionHandlerInterface
     */
    public function getHandler()
    {
        if ($this->handler !== NULL) {
            return $this->handler;
        }

        $method = 'get' . ucfirst($this->storage) . 'Handler';
        if (method_exists($this, $method)) {
            return $this->handler = $this->$method();
        }

        throw new ApplicationException('Session save handler ['
                . $this->storage . '] does not exist.', null, null, 929004);
    }

    /**
     * Returns Database session save handler.
     * 
     * @return \Nishchay\Session\SaveHandler\DBHandler
     */
    protected function getDbHandler()
    {
        return new DBHandler();
    }

    /**
     * Returns file session save handler.
     * 
     * @return \Nishchay\Session\SaveHandler\FileHandler
     */
    protected function getFileHandler()
    {
        return new FileHandler(Nishchay::getConfig('session.storagePath'));
    }

    /**
     * Returns memcache session save handler.
     * 
     * @return \Nishchay\Session\SaveHandler\MemcacheHandler
     */
    protected function getCacheHandler()
    {
        $config = Nishchay::getConfig('session.cache');
        return new CacheHandler($config);
    }

}
