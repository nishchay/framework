<?php

namespace Nishchay\Session;

use ArrayAccess;
use Nishchay\Exception\ApplicationException;
use Nishchay\Session\SaveHandler\SaveHandler;

/**
 * Base Session class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class BaseSession extends SaveHandler implements ArrayAccess
{

    /**
     * Session data.
     * 
     * @var array 
     */
    protected $session = [];

    /**
     * Session type name.
     * 
     * @var string 
     */
    protected $sessionType;

    /**
     * Initializes.
     * 
     * @param type $type
     */
    public function __construct($type)
    {
        $this->startSession()->init($type);
        $this->sessionType = $type;
    }

    /**
     * Initializes session variable.
     * 
     * @param   string      $type
     */
    protected function init($type)
    {
        !isset(static::$data[$type]) && static::$data[$type] = [];
        $this->session = &static::$data[$type];
    }

    /**
     * 
     * @param   string  $name
     * @return  mixed
     */
    public function &__get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * 
     * @param   string  $name
     * @param   mixed   $value
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * 
     * @param   string  $name
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * 
     * @param   string  $name
     */
    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    /**
     * Returns all session variable created.
     * 
     * @return type
     */
    public function getAll()
    {
        return $this->session;
    }

    /**
     * 
     * @param   string  $offset
     * @return  mixed
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->session);
    }

    /**
     * 
     * @param   string  $offset
     * @return  mixed
     */
    public function &offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->session[$offset];
        }

        return $this->throwNotExist($offset);
    }

    /**
     * Throws exception.
     * 
     * @param string $offset
     * @return null
     */
    protected function throwNotExist($offset)
    {
        throw new ApplicationException('Session property [' . $offset . '] not'
                . ' found in session type [' . $this->sessionType . '].', null, null, 929006);
    }

    /**
     * 
     * @param   string  $offset
     * @param   mixed   $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->session[] = $value;
        } else {
            $this->session[$offset] = $value;
        }
    }

    /**
     * 
     * @param   string  $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->session[$offset]);
    }

    /**
     * Starts session if not started
     */
    public final function startSession()
    {
        $this->initHandler();
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), 5.4, '>=') &&
                    session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            } else if (session_id() === '' || session_id() === NULL) {
                session_start();
            }
        }
        return $this;
    }

    /**
     * Returns session id.
     * 
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * Regenerates session id.
     * 
     * @param boolean $data Whether to remove exist session data.
     * @return boolean  TRUE on success or FALSE on failure.
     */
    public function regenerateSessionId($data = FALSE)
    {
        return session_regenerate_id($data);
    }

    /**
     * Destroys session.
     * 
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function destorySession()
    {
        return session_destroy();
    }

    /**
     * Returns string presentation of session data.
     * 
     * @return string
     */
    public function __toString()
    {
        return print_r($this->session, true);
    }

    /**
     * Returns session data.
     * 
     * @return array
     */
    public function __debugInfo()
    {
        return $this->session;
    }

}
