<?php

namespace Nishchay\Container;

use Nishchay;
use ReflectionClass;
use ReflectionMethod;
use Nishchay\Exception\ApplicationException;
use Nishchay\Utility\Coding;
use Nishchay\DI\DI;
use Nishchay\Data\EntityManager;
use Nishchay\Processor\FetchSingletonTrait;

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

    use FetchSingletonTrait;

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
        return $this->getInstance(ReflectionClass::class, [$this->containerClass]);
    }

    /**
     * Returns instance of container class.
     * 
     * @return object
     */
    private function getContainerInstance()
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

        if ($this->getReflection()->hasProperty('resolveList')) {
            $property = $this->getReflection()->getProperty('resolveList');
            $property->setAccessible(true);
            $resolveList = $property->getValue($this->getContainerInstance());

            if (is_array($resolveList) === false) {
                throw new ApplicationException('Property [' .
                        $this->containerClass . '::resolveList' . '] must'
                        . ' be array.', $this->containerClass, null, 934002);
            }

            $this->getDI()->set($resolveList);
        }


        if ($this->getReflection()->hasProperty('di') === false) {
            $this->getContainerInstance()->di = $this->getDI();
        }

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
            throw new ApplicationException('Method [' . $this->containerClass . '::' . $name . '] does'
                    . ' not exists in container.', $this->containerClass, null, 934003);
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
            $class = $method->invoke($this->getContainerInstance());
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
                throw new ApplicationException('Container method should return either instnace or class name.', $this->containerClass, $name, 934004);
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
                $this->getDI()->create($class, $arguments);
    }

}
