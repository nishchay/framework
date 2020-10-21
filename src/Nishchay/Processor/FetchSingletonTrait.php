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

    protected static $instances = [];

    /**
     * 
     * @param string $class
     * @return type
     */
    protected function getInstance(string $class, array $parameters = [])
    {
        if (isset(self::$instances[$class])) {
            return self::$instances[$class];
        }

        return self::$instances[$class] = new $class(...$parameters);
    }

}
