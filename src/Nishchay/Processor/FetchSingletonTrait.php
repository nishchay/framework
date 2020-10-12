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
    private static $instances = [];

    /**
     * Creates or returns instance of class.
     * 
     * @param string $class
     * @return object
     */
    private function getInstance(string $class, array $arguments = [])
    {
        if (isset(self::$instances[$class])) {
            return self::$instances[$class];
        }

        if (method_exists($class, __FUNCTION__)) {
            $instance = call_user_func_array([$class, __FUNCTION__], $arguments);
        } else {
            $instance = new $class(...$arguments);
        }

        return self::$instances[$class] = $instance;
    }

}
