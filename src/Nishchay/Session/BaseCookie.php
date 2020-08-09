<?php

namespace Nishchay\Session;

use Nishchay\Exception\ApplicationException;

/**
 * Base Cookie class
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class BaseCookie extends BaseSession
{

    /**
     *
     * @var type 
     */
    protected static $loaded = [];

    /**
     * Cookie expiry time in second.
     * 
     * @var int
     */
    private $expiry = 300;

    /**
     * Path where cookie only be available.
     * 
     * @var string 
     */
    private $path = '';

    /**
     * Domain where cookies should be available.
     * 
     * @var string
     */
    private $domain = '';

    /**
     * To allow cookie over HTTPS only.
     * 
     * @var bool 
     */
    private $secure = false;

    /**
     * To available through HTTP only.
     *  
     * @var bool
     */
    private $httpOnly = false;

    /**
     * 
     * @param type $type
     */
    public function __construct($type)
    {
        parent::__construct($type);
    }

    /**
     * 
     * @param type $type
     */
    protected function init($type)
    {
        self::$loaded[] = $type;
        $this->session = &$_COOKIE;
    }

    /**
     * Sets cookie.
     * 
     * @param type $offset
     * @param type $value
     */
    public function __set($offset, $value)
    {
        $this->offsetSet($offset, $value);
    }

    /**
     * Removes cookie.
     * 
     * @param type $offset
     */
    public function __unset($offset)
    {
        $this->offsetUnset($offset);
    }

    /**
     * Sets cookie.
     * 
     * @param   string  $offset
     * @param   mixed   $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new ApplicationException('Name is required to create cookie.', null, null, 929005);
        }

        setcookie($offset, $value, time() + $this->expiry, $this->path, $this->domain, $this->secure, $this->httpOnly);
        $this->session[$offset] = $value;
    }

    /**
     * Removes cookie.
     * 
     * @param type $offset
     */
    public function offsetUnset($offset)
    {
        setcookie($offset, '', -1);
    }

    /**
     * Set expiry time of cookie. Once this set it will be applied to all
     * future cookies.
     * 
     * @param int $expiry
     * @return $this
     */
    public function setExpiry(int $expiry): self
    {
        $this->expiry = $expiry;
        return $this;
    }

    /**
     * Path where cookie should only be available to. Once this set it will be applied to all
     * future cookies.
     * 
     * @param string $path
     * @return $this
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Domain and submain where cookie should be available to. Once this set it will be applied to all
     * future cookies.
     * 
     * @param string $domain
     * @return $this
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * To allow cookies over HTTPS only. Once this set it will be applied to all
     * future cookies.
     * 
     * @param string $secure
     * @return $this
     */
    public function setSecure(bool $secure): self
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * To allow cookie over HTTP protocol only. Once this set it will be applied to all
     * future cookies.
     * 
     * @param string $httpOnly
     * @return $this
     */
    public function setHttpOnly(bool $httpOnly): self
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }

}
