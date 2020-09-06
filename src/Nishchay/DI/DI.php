<?php

namespace Nishchay\DI;

use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\UnableToResolveException;
use ReflectionClass;

/**
 * Calling Dependency Injection.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DI extends Resolver
{

    /**
     * Reflection instance of object passed in constructor which
     * will be known as calling class.
     * 
     * @var object 
     */
    private $reflection;

    /**
     * Object passed in constructor.
     * 
     * @var object 
     */
    private $object = null;

    /**
     * Initialization
     * Creates reflection object from where this class get called
     * 
     * @param object $object
     */
    public function __construct($object = null)
    {
        if ($object !== null && is_object($object)) {
            $this->object = $object;
            $this->reflection = new ReflectionClass($object);
        }
    }

    /**
     * Returns value added into global access. If not found then throws exception.
     * 
     * @param   string      $name
     * @return  mixed
     * @throws  UnableToResolveException
     */
    private function getGlobalValue($name, $type, $exception = true)
    {
        if (DependencyList::exist($name)) {
            return DependencyList::get($name);
        }

        if ($exception) {
            throw new UnableToResolveException('Unable to resolve ' . $type . ' [' . $name . '].', null, null, 915001);
        }
        
        return null;
    }

    /**
     * Returns value from either of the following
     * 1. Added by set method of this class.
     * 2. Calling class property.
     * 3. Global property.
     * 
     * @param   string      $name
     * @return  mixed
     * @throws  UnableToResolveException
     */
    protected function getValue($name, $type = 'paramter', $exception = true)
    {
        return array_key_exists($name, $this->resolveList) ? $this->resolveList[$name] : $this->getGlobalValue($name, $type, $exception);
    }

    /**
     * Creates new instance of given by resolving parameter defined in 
     * constructor and start resolving another class if there type hinting.
     * 
     * @param   object|string   $class
     * @param   array           $resolveList
     * @param   boolean         $later
     * @return  object
     * @throws  UnableToResolveException
     */
    public function create($class, $resolveList = [], bool $later = false)
    {
        $instance = $this->getResolvedObject($class, $resolveList);
        if ($later === true) {
            $this->saveInstance($class, $instance);
        }
        return $instance;
    }

    /**
     * Invokes method on given object by resolving parameter
     * from property defined within calling class.
     * 
     * @param   object  $instnace
     * @param   string  $methodName
     * @param   array   $resolveList
     * @return  mixed
     * @throws  ApplicationException
     */
    public function invoke($instnace, string $methodName, array $resolveList = [])
    {
        $class = new ReflectionClass($instnace);
        if ($class->hasMethod($methodName)) {
            $method = $class->getMethod($methodName);
            if ($method->isPublic()) {
                return $method->invokeArgs($instnace, $this->getMethodParameter($method, $resolveList));
            }
            throw new ApplicationException('Method [' . $method->class . '::' . $methodName . ']' .
                    ' must be public to invoke.', null, null, 915002);
        }
        throw new ApplicationException('Method [' . $class->getName() .
                '::' . $methodName . '] does not exists.', null, null, 915003);
    }

}
