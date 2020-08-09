<?php

namespace Nishchay\Http\View;

/**
 * Abstract locator class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractLocator
{

    /**
     * Context name.
     * 
     * @var string 
     */
    protected $context;

    /**
     * Class name from where instance of this class created.
     * 
     * @var stirng 
     */
    protected $class;

    /**
     * Method name of class from where instance of this class created.
     * 
     * @var string 
     */
    protected $method;

    /**
     * 
     * @param type $context
     */
    public function __construct($context, $class, $method)
    {
        $this->setClass($class);
        $this->setMethod($method);
        $this->setContext($context);
    }

    /**
     * 
     * @param type $context
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * 
     * @param type $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * 
     * @param type $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * 
     * @param type $viewName
     */
    public abstract function getPath(string $viewName);
}
