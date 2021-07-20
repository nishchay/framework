<?php

namespace Nishchay\Container;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Processor\AbstractCollection;
use Nishchay\Persistent\System;
use Nishchay\Processor\Facade;
use ReflectionClass;
use Nishchay\Attributes\Container\Container as ContainerAttribute;

/**
 * Container collection class.
 *
 * @license     https://nishchay.io/license    New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Collection extends AbstractCollection
{

    /**
     * Collection.
     * 
     * @var array 
     */
    private $collection = [];

    /**
     * Lists of facades for the container classes.
     * 
     * @var array
     */
    private $facades = [];

    /**
     * Initialization
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Creates facade if not created.
     * 
     * @param string $class
     * @return nulll
     */
    private function checkFacade(string $class): void
    {
        if (isset($this->facades[$class]) === false) {
            return;
        }

        if (Facade::isExists($class)) {
            return;
        }

        $instance = $this->facades[$class];
        Facade::create($instance, $class);
    }

    /**
     * Saves instance to route persistent file if application in live stage
     * and updates flag to store mode.
     * 
     * @return NULL
     */
    private function init()
    {
        spl_autoload_register([$this, 'checkFacade']);
        if (Nishchay::isApplicationStageLive() && System::isPersisted('containers')) {
            $this->collection = System::getPersistent('containers');
            $this->facades = System::getPersistent('facades');
        }
    }

    /**
     * Stores class into collection.
     * 
     * @param string $class
     */
    public function store(string $class, array $attribtues)
    {
        $isContainer = false;
        foreach ($attribtues as $attribute) {
            if ($attribute->getName() === ContainerAttribute::class) {
                $isContainer = true;
            }
        }

        if ($isContainer === false) {
            throw new ApplicationException('Class [' . $class . '] must be container.',
                            $class, null, 934006);
        }

        $this->collection[$class] = new Container($class);
        $facade = $class . '::FACADE';
        if (defined($facade)) {

            $name = constant($facade);
            if (isset($this->facades[$name]) || Facade::isExists($name)) {
                throw new ApplicationException('Facade with name [' . $name . '] already exists.',
                                $class, null, 934005);
            }

            $this->facades[$name] = $this->collection[$class];
        }
    }

    /**
     * Returns container.
     * 
     * @param string $class
     * @return type
     * @throws NotSupportedException
     */
    public function get(string $class)
    {
        if (array_key_exists($class, $this->collection) === false) {
            throw new NotSupportedException('Class [' . $class . '] is not container.',
                            null, null, 934001);
        }

        return $this->collection[$class];
    }

    /**
     * Returns all containers.
     * 
     * @return type
     */
    public function getAll()
    {
        return $this->collection;
    }

    /**
     * Returns all facades.
     * 
     * @return array
     */
    public function getFacades()
    {
        return $this->facades;
    }

    /**
     * Returns true if class exists in collection.
     * 
     * @param string $class
     * @return bool
     */
    public function isExists(string $class): bool
    {
        return array_key_exists($class, $this->collection);
    }

    /**
     * Returns count of containers within application.
     * 
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }

}
