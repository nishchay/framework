<?php

namespace Nishchay\Http\Request;

use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\ApplicationException;
use ReflectionObject;

/**
 * Request store class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class RequestStore
{

    /**
     * Name of store property used in this class to
     * save request values.
     */
    const STORE_PROPERTY = 'vars';

    /**
     * Request store values.
     * 
     * @var array 
     */
    private static $vars = array();

    /**
     * 
     */
    public function __construct()
    {
        throw new NotSupportedException('Class [' . __CLASS__ . '] does not support'
                . ' instance to be created.', null, null, 920002);
    }

    /**
     * Checks name is string or not.
     * This method throws warning if name is not string type.
     * 
     * @param   string      $name
     * @return  boolean
     */
    private static function isValidName($name)
    {
        if (!is_string($name)) {
            throw new ApplicationException('Name should be variable for'
                    . ' RequestStore.', null, null, 920003);
        }

        return true;
    }

    /**
     * Checks value is added or not.
     * 
     * @param   string      $name
     * @param   boolean     $throw
     * @return  boolean
     */
    public static function isExist($name)
    {
        return array_key_exists($name, self::$vars);
    }

    /**
     * Adds variable to RequestStore.
     * First it checks $name already been added or not.
     * If the $name already been added, throws warning.
     * 
     * @param   string      $name
     * @param   mixed       $value
     * @return  boolean
     * @throws  warning
     */
    public static function add($name, $value = null, bool $supress = false)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                self::add($key, $value);
            }

            return true;
        }

        if (self::isValidName($name) === false) {
            return false;
        }

        if (self::isExist($name)) {
            if ($supress === false) {
                throw new ApplicationException($name . ' already exist.');
            }

            return false;
        } else {
            self::$vars[$name] = $value;
            return true;
        }
    }

    /**
     * Updates variable added in RequestStore
     * 
     * @param   string      $name
     * @param   mixed       $value
     * @return  boolean
     */
    public static function update(string $name, $value)
    {
        if (self::isExist($name)) {
            self::$vars[$name] = $value;
            return true;
        }

        return false;
    }

    /**
     * Adds and item if not exist otherwise update
     * 
     * @param   string      $name
     * @param   mixed       $value
     * @return  int Number of item added and updated.
     */
    public static function set($name, $value = null)
    {
        $names = is_array($name) ? $name : [$name => $value];

        $saved = 0;
        foreach ($names as $name => $value) {
            self::isValidName($name);

            if (self::isExist($name)) {
                self::update($name, $value) && ($saved++);
            } else {
                self::add($name, $value) && $saved++;
            }
        }

        return $saved;
    }

    /**
     * Removes variable added to RequestStore
     * 
     * @param   string      $name
     * @return  boolean
     */
    public static function remove(string $name)
    {
        if (self::isExist($name)) {
            unset(self::$vars[$name]);
            return true;
        }

        return false;
    }

    /**
     * Returns value of given key
     * 
     * @param   string              $name
     * @param   mixed               $default
     * @return  boolean|string
     */
    public static function get(string $name, $default = false)
    {
        if (self::isExist($name)) {
            return self::$vars[$name];
        } else {
            return $default;
        }

        return false;
    }

    /**
     * Returns all variable added to PageScope
     * 
     * @return array
     */
    public static function getAll()
    {
        return self::$vars;
    }

    /**
     * Returns the type of passed key's value.
     * Returns FALSE if the given key not added in request store.
     * 
     * @param   string              $name
     * @return  boolean
     */
    public static function getType($name)
    {
        if (!self::isExist($name)) {
            return false;
        } else {
            $type = gettype(self::$vars[$name]);
            if ($type === 'object') {
                $reflection = new ReflectionObject(self::$vars[$name]);
                return $reflection->getName();
            } else {
                return $type;
            }
        }
    }

}
