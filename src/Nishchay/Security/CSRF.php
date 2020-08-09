<?php

namespace Nishchay\Security;

use Processor;
use Nishchay\Exception\BadRequestException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Utility\StringUtility;
use Nishchay\Http\Request\Request;

/**
 * CSRF class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class CSRF
{

    /**
     * Prefix for session name where CSRF token will be saved.
     * 
     */
    const CSRF_PREFIX = 'csrf_';

    /**
     * CSRF token input name.
     * 
     * @var string 
     */
    private $name = 'csrf';

    /**
     * Where CSRF token should be received.
     * 
     * @var string 
     */
    private $where = Request::POST;

    /**
     * CSRF token value.
     * 
     * @var string
     */
    private $value = false;

    /**
     * CSRF token length.
     * 
     * @var int
     */
    private $length = 64;

    /**
     * CSRF token for.
     * 
     * @var string
     */
    private $for;

    /**
     * 
     * @param string $for
     */
    public function __construct($for)
    {
        $this->setFor($for);
    }

    /**
     * Returns CSRF token be for.
     * 
     * @return string
     */
    public function getFor()
    {
        return $this->for;
    }

    /**
     * Set token to be used for.
     * This should be form name.
     * 
     * @param string $for
     * @return $this
     */
    public function setFor($for)
    {
        $this->for = $for;
        return $this;
    }

    /**
     * Returns CSRF token input name.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets CSRF token input name.
     * 
     * @param type $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns location where CSRF token can be sent by client.
     * 
     * @return string
     */
    public function getWhere(): string
    {
        return $this->where;
    }

    /**
     * Sets location where CSRF token should be send.
     * 
     * @param string $where
     * @return $this
     */
    public function setWhere(string $where): self
    {
        $shouldBe = [Request::HEADER, Request::GET, Request::POST];
        if (!in_array(strtoupper($where), $shouldBe)) {
            throw new NotSupportedException('Invalid location for CSRF token.', null, null, 927003);
        }
        $this->where = strtoupper($where);
        return $this;
    }

    /**
     * Returns CSRF token value.
     * 
     * @return string
     */
    public function getValue(): string
    {
        if ($this->value !== false) {
            return $this->value;
        }

        # If CSRF already been stored in session we will use the same
        # otherwise we will create new.
        if ($this->getSessionValue() !== false) {
            $token = $this->getSessionValue();
        } else {
            $token = $this->getNewValue();
        }

        # Setting token to class property so that on next call we can
        # return the same.
        return $this->value = $token;
    }

    /**
     * Creates new CSRF token and stores it in session.
     * 
     * @return string
     */
    public function getNewValue(): string
    {
        $token = StringUtility::getRandomString($this->getLength(), true);
        $this->setSessionValue($token);
        return $token;
    }

    /**
     * Returns CSRF token from session.
     * 
     * @return string
     */
    private function getSessionValue()
    {
        return Processor::getInternalSessionValue(static::CSRF_PREFIX . $this->getFor());
    }

    /**
     * Sets token to internal session.
     * 
     * @param type $token
     */
    private function setSessionValue($token)
    {
        $this->value = $token;
        Processor::setInternalSessionValue(static::CSRF_PREFIX . $this->getFor(), $token);
    }

    /**
     * Returns CSRF token length.
     * 
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Returns CSRF token length.
     * 
     * @param string $length
     * @return $this
     */
    public function setLength(int $length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * Returns CSRF token from request.
     * 
     * @return string
     */
    private function getRequestToken()
    {
        switch ($this->getWhere()) {
            case Request::HEADER:
                return $this->getFromHeader($this->getName());
            case Request::GET:
                return Request::get($this->getName());
            case Request::POST:
                return Request::post($this->getName());
            default:
                return '';
        }
    }

    /**
     * Returns token from request header.
     * 
     * @param string $name
     * @return string
     */
    private function getFromHeader($name)
    {
        return Request::server('HTTP_' .
                        strtoupper(str_replace('-', '_', $name)));
    }

    /**
     * Returns TRUE if request CSRF match with CSRF stored in session.
     * 
     * @return boolean
     */
    public function verify()
    {
        if ($this->getRequestToken() !== $this->getValue()) {
            $this->setSessionValue(false);
            throw new BadRequestException('Invalid request.', null, null, 927004);
        }
        $this->setSessionValue(false);
        return true;
    }

    /**
     * Returns input tag with CSRF name and value as string.
     */
    public function __toString()
    {
        if ($this->getWhere() === Request::HEADER) {
            return $this->getNewValue();
        }

        return '<input'
                . ' type="hidden"'
                . ' name="' . $this->getName() . '"'
                . ' value="' . $this->getNewValue() . '" />';
    }

}
