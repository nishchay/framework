<?php

namespace Nishchay\Controller\Annotation;

use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\NotSupportedException;

/**
 * Exception handler annotation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ExceptionHandler extends BaseAnnotationDefinition
{

    /**
     * Type parameter value.
     * 
     * @var string
     */
    private $order = [];

    /**
     * ignore parameter value.
     * 
     * @var boolean 
     */
    private $ignore = false;

    /**
     *
     * @var type 
     */
    private $callback = false;

    /**
     * 
     * @param   string    $class
     * @param   string    $method
     * @param   array     $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->setter($parameter, 'parameter');
    }

    /**
     * Returns parameter value of type.
     * 
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets parameter value of type.
     * 
     * @param type $type
     */
    protected function setOrder($type)
    {
        $this->order = $type;
    }

    /**
     * Returns callback for exception handler.
     * 
     * @return type
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Sets exception handler callback.
     * 
     * @param string $callback
     */
    protected function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Return ignore parameter value.
     * 
     * @return boolean
     */
    public function getIgnore()
    {
        return $this->ignore;
    }

    /**
     * Sets ignore parameter value.
     * 
     * @param string|array $ignore
     */
    public function setIgnore($ignore)
    {
        throw new NotSupportedException('This parameter is not yet supported.', $this->class, $this->method, 914027);
    }

}
