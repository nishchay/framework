<?php

namespace Nishchay\Route\Annotation;

use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Annotation\BaseAnnotationDefinition;

/**
 * Forwarder annotation definition.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Forwarder extends BaseAnnotationDefinition
{

    /**
     * Allow or disallow route to accept any forwarded request.
     * 
     * @var boolean 
     */
    private $ascent = true;

    /**
     * Allow or disallow route to forward to any other route.
     * 
     * @var boolean 
     */
    private $descent = true;

    /**
     * Parameters defined within annotation.
     * 
     * @var type 
     */
    private $parameter = [];

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->parameter = $parameter;
        $this->setter($parameter, 'parameter');
    }

    /**
     * Returns ascent flag.
     * 
     * @return type
     */
    public function getAscent()
    {
        return $this->ascent;
    }

    /**
     * Returns descent flag.
     * 
     * @return type
     */
    public function getDescent()
    {
        return $this->descent;
    }

    /**
     * Sets ascent flag defined on annotation.
     * 
     * @param type $ascent
     */
    protected function setAscent($ascent)
    {
        if (!is_bool($ascent)) {
            throw new InvalidAnnotationParameterException('Annotation [forwarder] parameter name'
                    . ' [ascent] must be boolean.', $this->class, $this->method, 926001);
        }

        $this->ascent = $ascent;
    }

    /**
     * Sets descent flag defined on annotation.
     * 
     * @param type $discent
     */
    protected function setDescent($discent)
    {
        if (!is_bool($discent)) {
            throw new InvalidAnnotationParameterException('Annotation [forwarder] parameter name'
                    . ' [discent] must be boolean.', $this->class, $this->method, 926002);
        }

        $this->descent = $discent;
    }

}
