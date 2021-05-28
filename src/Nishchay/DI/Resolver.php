<?php

namespace Nishchay\DI;

use Exception;
use Nishchay\Exception\ApplicationException;
use ReflectionClass;
use ReflectionObject;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Resolver class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class Resolver extends InstanceList
{

    /**
     * Ignore even if the property of the class or addded into listing.
     * 
     * @var array 
     */
    protected $ignored = [];

    /**
     * Properties added 
     * 
     * @var array 
     */
    protected $resolveList = [];

    /**
     * Find value by it's name.
     * 
     * @param   string      $name
     * @return  mixed
     * @throws  Exception
     */
    abstract protected function getValue($name, $type = 'parameter');

    /**
     * Adds dependency to resolve list.
     * 
     * @param   string      $name
     * @param   string      $value
     * @return  null
     */
    public function set($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $propertyName => $propertyValue) {
                $this->resolveList[$propertyName] = $propertyValue;
            }
            return $this;
        }

        $this->resolveList[$name] = $value;
        return $this;
    }

    /**
     * Removes dependency from resolve list.
     * 
     * @param string $name
     */
    public function remove(string $name)
    {
        if (is_string($name) && array_key_exists($name, $this->resolveList)) {
            unset($this->resolveList[$name]);
        }
        return $this;
    }

    /**
     * Resolves parameter to its value.
     * 
     * @param   string $class
     * @return
     */
    protected function getResolvedObject($class, $params = [])
    {
        # If we have instnace of $class, we should return it directly.
        if (($instance = $this->getInstance($class)) !== false) {
            return $instance;
        }

        $instead = $params[$class] ?? $this->getValue($class, 'parameter', false);
        $reflection = new ReflectionClass($instead ? $instead : $class);
        if (($constructor = $reflection->getConstructor()) !== null) {
            return $reflection->newInstanceArgs($this->getMethodParameter($constructor, $params));
        } else {
            return $reflection->newInstance();
        }
    }

    /**
     * Resolves method parameter and resolves it
     * 
     * @param   ReflectionMethod    $reflection
     * @return  array
     */
    protected function getMethodParameter(ReflectionMethod $reflection, $params = [])
    {
        $parameter = [];
        foreach ($reflection->getParameters() as $param) {
            try {

                # Will consider $params first.
                if (array_key_exists($param->name, $params)) {
                    $value = $params[$param->name];
                } else {
                    $value = $this->getValue($param->name);
                }
            } catch (Exception $ex) {
                # Will use optional value if its optional parameter.
                if ($param->isOptional()) {
                    $value = $param->getDefaultValue();
                } else {
                    # Let's create new object if there is type hinting for 
                    # parameter. $param->getClass() returns type hinted class 
                    # name or it returns NULL if there is no type hint.
                    if ($param->getType() === null) {
                        throw new ApplicationException($ex->getMessage(), null, null, 915015);
                    }
                    $value = $this->getResolvedObject($class->name, $params);
                }
            }

            $parameter[] = $value;
        }
        return $parameter;
    }

    /**
     * Resolve given property of object to it's value.
     * 
     * @param   object      $instnace
     * @param   string      $name
     * @return  boolean
     */
    public function resolveProperty($instnace, $name)
    {
        $reflection = new ReflectionObject($instnace);
        if ($reflection->hasProperty($name)) {
            $this->resolvePropertyToValue($instnace, $reflection->getProperty($name));
            return true;
        }
        return false;
    }

    /**
     * Resolve property to assign value.
     * 
     * @param   string              $object
     * @param   ReflectionProperty  $reflection
     */
    protected function resolvePropertyToValue($object, ReflectionProperty $reflection)
    {
        $reflection->setAccessible(true);
        $reflection->setValue($object, $this->getValue($reflection->name, 'class property'));
    }

    /**
     * Resolves all property of given object's class. 
     * Property passed in $ignore are not resolved and property exist in 
     * ignore list which was set using setIgnored method.
     * 
     * @param   object          $instance
     * @param   boolean|array   $ignore
     * @return  boolean
     * @throws  Exception
     */
    public function resolveAllProperty($instance, $ignore = true)
    {
        $reflection = new ReflectionObject($instance);
        foreach ($reflection->getProperties() as $property) {
            # We will not resolve property which are send in ignore parameter.
            if (is_array($ignore) && in_array($property->name, $ignore)) {
                continue;
            }

            # if property name is set to ignore we will ignore that to.
            if (array_key_exists($property->name, $this->ignored)) {
                continue;
            }

            $this->resolvePropertyToValue($instance, $property);
        }

        return true;
    }

    /**
     * Puts key into ignorance list. 
     * Key added into this listing will be ignored while resolving parameter.
     * 
     * @param   string      $key
     * @return  boolean
     */
    public function setIgnored(string $key)
    {
        return $this->ignored[$key] = true;
    }

    /**
     * Removes key added into ignorance list.
     * 
     * @param   string      $key
     * @return  boolean
     */
    public function removeIgnored(string $key)
    {
        if (array_key_exists($key, $this->ignored)) {
            unset($this->ignored[$key]);
            return true;
        }
        return false;
    }

}
