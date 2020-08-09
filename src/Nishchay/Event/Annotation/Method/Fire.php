<?php

namespace Nishchay\Event\Annotation\Method;

use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\InvalidAnnotationParameterException;

/**
 * Event Intended annotation
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Fire extends BaseAnnotationDefinition
{

    /**
     * Constant for before event.
     * 
     */
    const BEFORE = 'before';

    /**
     * Constant for after event.
     */
    const AFTER = 'after';

    /**
     * Parameters of annotations.
     * 
     * @var array 
     */
    private $parameter = [];

    /**
     * When to execute event.
     * 
     * @var boolean|string 
     */
    private $when = false;

    /**
     * Flag for executing event once or every time.
     * 
     * @var boolean 
     */
    private $once = false;

    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->parameter = $parameter;
        $this->setter($parameter, 'parameter');
    }

    /**
     * 
     * @return boolean|string
     */
    public function getWhen()
    {
        return $this->when;
    }

    /**
     * 
     * @param string $when
     */
    protected function setWhen($when)
    {
        if (!is_string($when) || !in_array($when, [static::AFTER, static::BEFORE])) {
            throw new InvalidAnnotationParameterException('Annotation [fire]'
                    . 'parameter name [when] should be after/before.', $this->class, $this->method, 916001);
        }
        $this->when = $when;
    }

    /**
     * Returns TRUE if event is allowed to execute only once during
     * lifetime of request.
     * 
     * @return type
     */
    public function getOnce()
    {
        return $this->once;
    }

    /**
     * Sets whether to execute event only once during lifetime of request.
     * 
     * @param boolean $once
     */
    protected function setOnce($once)
    {
        $this->once = (bool) $once;
    }

}
