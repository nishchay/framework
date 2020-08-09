<?php

namespace Nishchay\Data\Reflection;

/**
 * Data method class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DataMethod
{

    /**
     * Name of method's class.
     * 
     * @var string 
     */
    private $class;

    /**
     * Name of method.
     * 
     * @var string 
     */
    private $name;

    /**
     * 
     * @param   string  $class
     * @param   string  $name
     */
    public function __construct($class, $name)
    {
        $this->class = $class;
        $this->name = $name;
    }

    /**
     * Returns the name of method's class.
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Returns name of method.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
