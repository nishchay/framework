<?php

namespace Nishchay\Processor;

/**
 * Description of FetchSingletonTrait
 *
 * @author bpatel
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
