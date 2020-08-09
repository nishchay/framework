<?php

namespace Nishchay\Utility;

use Nishchay\Exception\ClassNotFoundException;
use Nishchay\Exception\ApplicationException;
use ReflectionMethod;
use Nishchay\Utility\StringUtility;

/**
 * Coding utility class.
 *
 * @license     http:#Nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Coding
{

    /**
     * Create class name alias of given class.
     * 
     * @param   string    $original
     * @param   string    $name
     */
    public static function createClassAlias($original, $name)
    {
        if (class_exists($name)) {
            throw new ClassNotFoundException('Class [' . $name . '] already exists.', 1, null, 930001);
        }

        eval("class {$name} extends $original {}");
    }

    /**
     * Finds file from root,
     * RESOURCES or APP directory.
     * 
     * @param   string      $path
     * @return  boolean
     */
    public static function fileLookUp($path)
    {
        if (file_exists($path)) {
            return $path;
        }
        $lookup = ['', ROOT, RESOURCES];
        if (defined('APP')) {
            $lookup[] = APP;
        }
        foreach ($lookup as $value) {
            if (file_exists($value . $path)) {
                return $value . $path;
            }
        }

        return FALSE;
    }

    /**
     * Converts type of value to their actual type.
     *  
     * @param   mixed   $value
     * @return  mixed
     */
    public static function toActualType($value)
    {
        $lowerValue = strtolower($value);
        $tranformable = ['null' => NULL, 'false' => FALSE, 'true' => TRUE];

        # Converting to actual value if it is present
        if (array_key_exists($lowerValue, $tranformable)) {
            return $tranformable[$lowerValue];
        } else {
            return $value;
        }
    }

    /**
     * Returns true if method is static or defined on parent class and
     * not starting with underscore.
     * 
     * @param   ReflectionMethod $reflection
     * @return  boolean
     */
    public static function isIgnorable(ReflectionMethod $reflection, $class)
    {
        # Ignore  method  if starting with underscore.
        if (strpos($reflection->name, '_') === 0) {
            return TRUE;
        }
        # Ignore staic method
        else if ($reflection->isStatic()) {
            return TRUE;
        }
        # Ignore method defined on parent class.
        else if ($reflection->class !== $class) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * R
     * 
     * @param   string      $value
     * @return  boolean
     */
    public static function isUnSerializable($value)
    {
        if (!is_string($value)) {
            return false;
        }
        $value = trim($value);
        if ($value == 'N;') {
            return true;
        }
        if (strlen($value) < 4 || $value[1] !== ':') {
            return false;
        }

        $semicolon = strpos($value, ';');
        $brace = strpos($value, '}');

        # Serialized srting contains ; and }
        if ($semicolon === false && $brace === false) {
            return false;
        }

        # But neither must be in the first X characters.
        if ($semicolon !== false && $semicolon < 3) {
            return false;
        }
        if (false !== $brace && $brace < 4) {
            return false;
        }
        $token = $value[0];
        switch ($token) {
            case 's':
                if (false === strpos($value, '"')) {
                    return false;
                }
            # or else fall through
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $value);
            case 'b':
            case 'i':
            case 'd':
                return (bool) preg_match("/^{$token}:[0-9.E-]+;/", $value);
        }
        return false;
    }

    /**
     * Serializes string.
     * 
     * @param object $object
     * @param boolean $binToHex
     * @return string
     */
    public static function serialize($object, bool $binToHex = false)
    {
        $serialized = serialize($object);

        return $binToHex ? bin2hex($serialized) : $serialized;
    }

    /**
     * Unserialize string.
     * It also converts hex to bin if input string hex.
     * 
     * @param string $value
     * @return mixed
     */
    public static function unserialize($value)
    {
        if (gettype($value) === 'resource') {
            $value = pack('h*', fgets($value));
            if (Coding::isUnSerializable($value) === false) {
                return null;
            }
        }
        return unserialize($value);
    }

    /**
     * Returns all property with value from the class as array.
     * 
     * @param   object  $object
     * @return  array
     */
    public static function getAsArray($object)
    {
        $property = [];
        $start = strlen(get_class($object)) + 2;
        foreach ((array) $object as $key => $value) {
            $newKey = substr($key, $start);
            $property[$newKey] = $value;
        }
        return $property;
    }

    /**
     * Returns all property with the value from the class as object.
     * 
     * @param   object  $object Should be an object of any class.
     * @return  array
     */
    public static function getAsObject($object)
    {
        return (object) self::getAsArray($object);
    }

    /**
     * Executes callback.
     * 
     * @param string|array $method    should be in class::method or
     *                                  [class,method] format or closure.
     * @param array $parameter          Optional. Should be array.
     * @return mixed
     */
    public static function invokeMethod($method, $parameter = [])
    {
        if (is_string($method)) {
            $method = explode('::', $method);
        }

        if (is_array($method)) {
            if (!isset($method[0]) || !isset($method[1])) {
                throw new ApplicationException('When first argument for method ['
                        . __METHOD__ . '] is array it should contain class or object as'
                        . ' first element and method name being second element.', 1, null, 930002);
            }
            $method[0] = is_string($method[0]) ? new $method[0] : $method[0];
        }

        return call_user_func_array($method, $parameter);
    }

    /**
     * Returns TRUE if callback exist.
     * 
     * @param   string  $callback
     * @return  boolean
     */
    public static function isCallbackExist($callback)
    {
        if (is_string($callback)) {
            $callback = explode('::', $callback);
        }

        return (isset($callback[0]) && isset($callback[1]) &&
                method_exists($callback[0], $callback[1]));
    }

    /**
     * Returns JSON encode string.
     * 
     * @param   array|object $array
     * @return  string
     */
    public static function encodeJSON($array, $options = 0, $depth = 512)
    {
        return json_encode($array, $options, $depth);
    }

    /**
     * Returns decoded JSON.
     * 
     * @param   string $json
     * @return  object
     */
    public static function decodeJSON($json, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($json, $assoc, $depth, $options);
    }

    /**
     * Returns namespace of the class.
     * 
     * @param type $fullClassName
     * @param type $className
     * @return type
     */
    public static function getNamespace($fullClassName, $className)
    {
        return substr($fullClassName, 0, strpos($fullClassName, $className));
    }

    /**
     * Returns base name of class.
     * 
     * @param string $class
     * @return string
     */
    public static function getClassBaseName($class)
    {
        return StringUtility::getExplodeLast('\\', $class);
    }

    /**
     * Converts JSON object to array
     * 
     * @param \stdClass $array
     * @return array
     */
    public static function toArray($array)
    {
        return json_decode(json_encode($array), JSON_OBJECT_AS_ARRAY);
    }

}
