<?php

namespace Nishchay\DI;

/**
 * InstanceList
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class InstanceList
{

    /**
     * Instance list.
     * 
     * @var array 
     */
    private static $list = [];

    /**
     * Adds given instance to list.
     * 
     * @param   string  $class
     * @param   object  $instance
     */
    protected function saveInstance($class, $instance)
    {
        if (array_key_exists($class, self::$list)) {
            return;
        }

        self::$list[$class] = $instance;
    }

    /**
     * Returns instance of given class if it has been instantiated by DI.
     * 
     * @param   string              $class
     * @return  boolean|object
     */
    public function getInstance($class)
    {
        if (array_key_exists($class, self::$list)) {
            return self::$list[$class];
        }
        return false;
    }

    /**
     * Removes class from listing if it has been added.
     * 
     * @param   string      $class
     * @return  boolean
     */
    public function removeInstance($class)
    {
        if (array_key_exists($class, self::$list)) {
            unset(self::$list[$class]);
            return true;
        }
        return false;
    }

}
