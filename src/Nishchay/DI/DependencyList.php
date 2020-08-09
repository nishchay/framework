<?php

namespace Nishchay\DI;

use Nishchay\Exception\UnableToResolveException;

/**
 * Description of Global Dependency value.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DependencyList
{

    /**
     * Global Property.
     * 
     * @var array 
     */
    private static $list = [];

    /**
     * Adds dependency.
     * 
     * @param   string  $name
     * @param   mixed   $value
     * @return  null
     */
    public static function set($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                static::$list[$key] = $value;
            }
            return true;
        }

        static::$list[$name] = $value;
        return true;
    }

    /**
     * Returns dependency value of given name.
     * Returns all dependency if $name parameter omitted.
     * 
     * @param   string  $name
     * @return  mixed
     */
    public static function get($name = null)
    {
        if ($name === null) {
            return static::$list;
        }

        if (static::exist($name)) {
            return static::$list[$name];
        }

        throw new UnableToResolveException('Depedency [' . $name . '] does not exist.', null, null, 915004);
    }

    /**
     * Returns true if dependency exist in list.
     * 
     * @param   string      $name
     * @return  boolean
     */
    public static function exist($name)
    {
        return array_key_exists($name, static::$list);
    }

    /**
     * Removes dependency of given name.
     * 
     * @param string $name
     */
    public static function remove($name)
    {
        if (static::exist($name)) {
            unset(static::$list[$name]);

            return false;
        }

        return true;
    }

}
