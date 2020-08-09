<?php

namespace Nishchay\Session;

use Nishchay\Exception\ApplicationException;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Conditional Session class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Conditional extends BaseSession
{

    use MethodInvokerTrait;

    /**
     * Read check callback.
     * 
     * @var string 
     */
    private $readCheck;

    /**
     * Save check callback.
     * 
     * @var string 
     */
    private $saveCheck;

    /**
     * Initializes.
     * 
     * @param string $readCheck
     * @param string $saveCheck
     */
    public function __construct(string $readCheck, string $saveCheck)
    {
        $this->readCheck = $readCheck;
        $this->saveCheck = $saveCheck;
        parent::__construct('conditional');
    }

    /**
     * Returns session value read check callback.
     * 
     * @return string
     */
    public function getReadCheck()
    {
        return $this->readCheck;
    }

    /**
     * Returns session value save check callback.
     * 
     * @return string
     */
    public function getSaveCheck()
    {
        return $this->saveCheck;
    }

    /**
     * Sets read check callback for future session value.
     * 
     * @param string $readCheck
     * @return $this
     */
    public function setReadCheck(string $readCheck): self
    {
        $this->readCheck = $readCheck;
        return $this;
    }

    /**
     * 
     * @param type $saveCheck
     * @return $this
     */
    public function setSaveCheck(string $saveCheck): self
    {
        $this->saveCheck = $saveCheck;
        return $this;
    }

    /**
     * 
     * @param   string  $offset
     * @param   mixed   $value
     */
    public function offsetSet($offset, $value)
    {
        $readCheck = $this->readCheck;
        $saveCheck = $this->saveCheck;
        if ($this->offsetExists($offset)) {
            $session = parent::offsetGet($offset);
            $readCheck = $session['readCheck'];
            $saveCheck = $session['saveCheck'];
        }

        if ($this->invokeMethod($saveCheck, [$offset, $value]) !== true) {
            throw new ApplicationException('Cannot set session value as save check failed'
                    . ' by [' . $saveCheck . '].', null, null, 929007);
        }
        $session_value = [
            'readCheck' => $readCheck,
            'saveCheck' => $saveCheck,
            'value' => $value
        ];

        parent::offsetSet($offset, $session_value);
    }

    /**
     * 
     * @param   string  $offset
     * @return  mixed
     */
    public function &offsetGet($offset)
    {
        $session = parent::offsetGet($offset);
        if (!is_array($session)) {
            return null;
        }

        if ($this->invokeMethod($session['readCheck'],
                        [$offset, $session['value']]) !== true) {
            throw new ApplicationException('Cannot get session value as read check'
                    . ' failed by [' . $session['readCheck'] . '].', null, null, 929008);
        }

        return $this->session[$offset]['value'];
    }

    /**
     * Returns read check callback for given session name.
     * 
     * @param string $name
     * @return string
     */
    public function getReadCheckOf($name)
    {
        $session = parent::offsetGet($name);
        if (!is_array($session)) {
            return null;
        }

        return $session['readCheck'];
    }

    /**
     * Returns save check callback for given session name.
     * 
     * @param string $name
     * @return string
     */
    public function getSaveCheckOf($name)
    {
        $session = parent::offsetGet($name);
        if (!is_array($session)) {
            return null;
        }

        return $session['saveCheck'];
    }

}
