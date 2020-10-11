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

    private static $instances = [];

    /**
     * 
     * @param string $class
     * @return type
     */
    private function getInstance(string $class, array $parameters = [])
    {
        if (isset(self::$instances[$class])) {
            return self::$instances[$class];
        }

        return self::$instances[$class] = new $class(...$parameters);
    }

}
