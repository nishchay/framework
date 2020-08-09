<?php

namespace Nishchay\Processor;

use Nishchay\Exception\NotSupportedException;


/**
 * Abstract singleton class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractSingleton
{

    private static $instance;

    protected function __construct()
    {
        ;
    }

    /**
     * This method is not declared as abstract to not force extending class
     * to implement.
     */
    abstract protected function onCreateInstance();

    /**
     * Prevents clone.
     * 
     * @throws NotSupportedException
     */
    private function __clone()
    {
        throw new NotSupportedException('Class [' . get_called_class() . '] is singleton.', null, null, 925024);
    }

    /**
     * Prevents wake up.
     * 
     * @throws NotSupportedException
     */
    private function __wakeup()
    {
        throw new NotSupportedException('Class [' . get_called_class() . '] is singleton.', null, null, 925025);
    }

    /**
     * Returns instance of class.
     * 
     * @return object
     */
    public static function getInstance()
    {
        if (static::$instance !== null) {
            return static::$instance;
        }

        static::$instance = new static;
        static::$instance->onCreateInstance();
        return static::$instance;
    }

}
