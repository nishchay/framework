<?php

namespace Nishchay\Processor;

/**
 * FetchSingletonTrait trait.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 */
trait FetchSingletonTrait
{

    /**
     * Instances list.
     * 
     * @var object 
     */
    protected static $instances = [];

    /**
     * Creates or returns instance of class.
     * 
     * @param string $class
     * @return object
     */
    protected function getInstance(string $class, array $parameters = [])
    {
        if (isset(self::$instances[$class])) {
            return self::$instances[$class];
        }

        if (method_exists($class, __FUNCTION__)) {
            $instance = call_user_func_array([$class, __FUNCTION__], $parameters);
        } else {
            $instance = new $class(...$parameters);
        }

        return self::$instances[$class] = $instance;
    }

}
