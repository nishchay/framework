<?php

namespace Nishchay\DI;

use Exception;
use Nishchay\Exception\UnableToResolveException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use ReflectionObject;
use Closure;

/**
 * Class Extender Dependency Injection.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Extender
{

    /**
     * Reflection object to extending class.
     * 
     * @var ReflectionObject 
     */
    private $reflection;

    /**
     * Added methods.
     * 
     * @var array 
     */
    private $methods = [];

    /**
     *
     * @var array 
     */
    private $properties = [];

    /**
     * Access type of method should be.
     * 
     * @var array 
     */
    protected $methodShouldBe = [];

    /**
     * Access type of property should be.
     * 
     * @var array 
     */
    protected $propertyShouldBe = [];

    /**
     * Calls added method if exist.
     * 
     * @param   string      $name
     * @param   array       $arguments
     * @return  mixed
     */
    public function __call($name, $arguments)
    {
        if (!array_key_exists($name, $this->methods) ||
                !$this->isAccessible(current(debug_backtrace()), $this->methods[$name]['access'])) {
            throw new UnableToResolveException('Undefined method [' .
                    get_class($this) . '::' . $name . '].', null, null, 915005);
        }

        $closure = $this->methods[$name]['closure'];
        if (is_callable($closure)) {
            $returned = call_user_func_array($closure, $arguments);
            return $returned;
        }
    }

    /**
     * 
     * @param   string  $name
     * @return  type
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->properties) ||
                !$this->isAccessible(current(debug_backtrace()), $this->properties[$name]['access'])) {
            throw new UnableToResolveException('Undefined property [' . get_class($this) . '::' . $name . '].', null, null, 915006);
        }
        return $this->properties[$name]['value'];
    }

    /**
     * Sets property value if added by addProperty method.
     * 
     * @param   string      $name
     * @param   mixed       $value
     * @throws  Exception
     */
    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->properties) ||
                !$this->isAccessible(current(debug_backtrace()), $this->properties[$name]['access'])) {
            throw new UnableToResolveException('Undefined property [' . get_class($this) . '::' . $name . '].', null, null, 915007);
        }

        $this->properties[$name]['value'] = $value;
    }

    /**
     * Add method to class.
     * 
     * @param   string          $name
     * @param   Closure        $closure
     * @param   string          $access
     * @throws  Exception
     */
    public function addMethod($name, Closure $closure, $access = 'public')
    {
        if ($this->isMethodExist($name) &&
                $this->isSupportedAccessType($access) &&
                $this->isValidAccessType('method', $name, $access)) {
            if ($closure instanceof Closure) {
                $this->methods[$name] = [
                    'access' => $access, 'closure' => $closure];
                return true;
            }
        }


        throw new ApplicationException('Can not add method either method'
                . ' already exists or its access type not valid.', null, null, 915008);
    }

    /**
     * Adds property to class.
     * 
     * @param   stirng      $name
     * @param   mixed       $value
     * @param   string      $access
     * @return  boolean
     * @throws  Exception
     */
    public function addProperty($name, $value, $access = 'public')
    {
        if (isset($this->{$name}) || array_key_exists($name, $this->properties)) {
            throw new ApplicationException('Property [' . $name . '] already exist.', null, null, 915009);
        }

        if ($this->isSupportedAccessType($access) &&
                $this->isValidAccessType('property', $name, $access)) {
            $this->properties[$name] = ['access' => $access, 'value' => $value];
            return true;
        }
    }

    /**
     * Is accessible from outside.
     * 
     * @param   array       $trace
     * @param   string      $access
     * @return  boolean
     */
    private function isAccessible($trace, $access)
    {
        if ($this->reflection === NULL) {
            $this->reflection = new ReflectionObject($this);
        }

        if ($access !== 'public' && $trace['file'] !== $this->reflection->getFileName()) {
            return false;
        }

        return true;
    }

    /**
     * Is given method name exist in added methods.
     * 
     * @param   string      $name
     * @return  boolean
     * @throws  Exception
     */
    private function isCustomMethodExist($name)
    {
        if (!array_key_exists($name, $this->methods)) {
            throw new ApplicationException('Method [' . $name . '] not exist'
                    . ' or not added by Extender.', null, null, 915010);
        }
        return true;
    }

    /**
     * Is method exist in extending class or added method.
     * 
     * @param   string      $name
     * @return  boolean
     * @throws  Exception
     */
    private function isMethodExist($name)
    {
        if (method_exists($this, $name) || array_key_exists($name, $this->methods)) {
            throw new ApplicationException('Method [' . static::class .
                    '::' . $name . '] already exists.', null, null, 915011);
        }
        return true;
    }

    /**
     * Is given access type supported or not.
     * Supported access type are private and public.
     * 
     * @param   string      $access
     * @return  boolean
     * @throws  Exception
     */
    private function isSupportedAccessType($access)
    {
        if (!in_array($access, array('private', 'public'))) {
            throw new NotSupportedException('Access type [' .
                    $access . '] not supported.', null, null, 915012);
        }
        return true;
    }

    /**
     * Is $type(method/property) is valid based on access type of 
     * should be same as told by extending class.
     * 
     * @param   string      $type
     * @param   string      $name
     * @param   string      $access
     * @return  boolean
     */
    private function isValidAccessType($type, $name, $access)
    {
        if ($type === 'method') {
            if (array_key_exists($name, $this->methodShouldBe) &&
                    $this->methodShouldBe[$name] !== $access) {
                throw new NotSupportedException('Method ' . get_class($this) . '::' .
                        $name . ' should be [' . $this->methodShouldBe[$name] . '].', null, null, 915013);
            }
            return true;
        }

        if (array_key_exists($name, $this->propertyShouldBe) &&
                $this->propertyShouldBe[$name] !== $access) {
            throw new NotSupportedException('Property ' . get_class($this) . '::' .
                    $name . ' should be [' . $this->methodShouldBe[$name] . '].', null, null, 915014);
        }

        return true;
    }

    /**
     * Removes added method.
     * 
     * @param string $name
     */
    public function removeMethod($name)
    {
        if (array_key_exists($name, $this->methods)) {
            unset($this->methods[$name]);
            return true;
        }
        return false;
    }

    /**
     * Sets access specifier of added method.
     * It fails to set if extending class told to set fix.
     * 
     * @param   string      $name
     * @param   string      $access
     * @throws  Exception
     */
    public function setAccessSpecifier($name, $access)
    {
        if ($this->isCustomMethodExist($name) &&
                $this->isSupportedAccessType($access) &&
                $this->isValidAccessType('method', $name, $access)) {
            $this->methods[$name]['access'] = $access;
        }
        return true;
    }

}
