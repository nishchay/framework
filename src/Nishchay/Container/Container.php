<?php

namespace Nishchay\Container;

use Nishchay;
use ReflectionClass;
use ReflectionMethod;
use Nishchay\Exception\ApplicationException;
use Nishchay\Utility\Coding;
use Nishchay\DI\DI;
use Nishchay\Data\EntityManager;

/**
 * Container class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Container
{

    /**
     * Container class name.
     * 
     * @var string
     */
    private $containerClass;

    /**
     * Reflection instance for container class.
     * 
     * @var string 
     */
    private $reflection;

    /**
     * Instance of container class.
     * 
     * @var object
     */
    private $instance;

    /**
     * Instance of DI.
     * 
     * @var DI
     */
    private $di;

    /**
     * List of methods in container class.
     * 
     * @var array 
     */
    private $methods = [];

    /**
     * List of method instances.
     * 
     * @var array 
     */
    private $methodInstances = [];

    /**
     * Initialization.
     * 
     * @param string $containerClass
     */
    public function __construct(string $containerClass)
    {
        $this->init($containerClass);
    }

    /**
     * Returns reflection instance for container class.
     * 
     * @return ReflectionClass
     */
    private function getReflection(): ReflectionClass
    {
        if ($this->reflection !== null) {
            return $this->reflection;
        }

        return $this->reflection = new ReflectionClass($this->containerClass);
    }

    /**
     * Returns instance of container class.
     * 
     * @return object
     */
    private function getInstance()
    {
        if ($this->instance !== null) {
            return $this->instance;
        }

        return $this->instance = $this->getDI()->create($this->containerClass);
    }

    /**
     * 
     * @return type
     */
    private function getDI()
    {
        if ($this->di !== null) {
            return $this->di;
        }

        return $this->di = new DI();
    }

    /**
     * Iterates over each method to find container method.
     * 
     * @param string $containerClass
     */
    public function init(string $containerClass)
    {
        $this->containerClass = $containerClass;

        foreach ($this->getReflection()->getMethods() as $method) {
            if (Coding::isIgnorable($method, $this->containerClass)) {
                continue;
            }

            $this->storeMethod($method);
        }
    }

    /**
     * Stores container methods.
     * 
     * @param ReflectionMethod $method
     * @return boolean
     * @throws ApplicationException
     */
    private function storeMethod(ReflectionMethod $method)
    {
        # Method must be public and it should start with get keyword.
        if ($method->isPublic() === false || strpos($method->name, 'get') !== 0) {
            return false;
        }

        return $this->methods[$method->name] = true;
    }

    /**
     * 
     * @param type $name
     * @param type $arguments
     * @return boolean
     * @throws ApplicationException
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') !== 0 || array_key_exists($name, $this->methods) === false) {
            throw new ApplicationException('Method [' . $name . '] does'
                    . ' not exists in container class [' . $this->containerClass . '].');
        }

        return $this->getMethodInstance($name, ...$arguments);
    }

    /**
     * Returns instance of container based on its method.
     * 
     * @param string $name
     * @param bool $new
     * @return object
     */
    private function getMethodInstance(string $name, bool $new = false)
    {
        if ($new === false && isset($this->methodInstances[$name])) {
            return $this->methodInstances[$name];
        }

        if ($this->methods[$name] === true) {
            $method = new ReflectionMethod($this->containerClass, $name);
            $class = $method->invoke($this->getInstance());
        } else {
            $class = $this->methods[$name];
        }

        # Container method can reurn class name as string, in that case we will
        # create instnace of returned class name.
        switch (true) {
            case is_string($class):
                $instnace = $this->createInstance($class, array_slice(func_get_args(), 2));
                break;
            case is_object($class):
                $instnace = $class;
                break;
            default:
                throw new ApplicationException('Container method should return either instnace or class name.', $this->containerClass, $name);
                break;
        }
        return $new ? $instnace : $this->methodInstances[$name] = $instnace;
    }

    /**
     * Creates instance of class.
     * 
     * @param string $class
     * @param array $arguments
     * @return type
     */
    private function createInstance(string $class, array $arguments)
    {
        return Nishchay::getEntityCollection()->isExist($class) ?
                new EntityManager($class) :
                $this->di->create($class, $arguments[0] ?? []);
    }

}
