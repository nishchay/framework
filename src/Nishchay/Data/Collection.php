<?php

namespace Nishchay\Data;

use Nishchay;
use Nishchay\Processor\AbstractCollection;
use Nishchay\Persistent\System;
use Nishchay\Attributes\Entity\Entity;
use Nishchay\Exception\ApplicationException;

/**
 * Entity collection class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Collection extends AbstractCollection
{

    const DIR = 'entities';

    /**
     * Entities collection.
     * 
     * @var array 
     */
    private $collection = [];

    /**
     * 
     */
    public function __construct()
    {
        # Fetching collection from persitent if its been persisted.
        if (System::isPersisted('entities')) {
            $this->collection = System::getPersistent('entities');
        }
    }

    /**
     * Registering entity to collection.
     * 
     * @param string $class
     */
    public function register(string $class, array $attributes): void
    {
        $isEntity = false;
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === Entity::class) {
                $isEntity = true;
            }
        }

        if ($isEntity === false) {
            throw new ApplicationException('[' . $class . '] must be entity.',
                            $class, null, 911103);
        }

        $this->checkStoring();
        $this->collection[$class] = false;
    }

    /**
     * Returns TRUE when passed class_name is registered as entity.
     * 
     * @param   string      $class
     * @return  boolean
     */
    public function isExist(string $class): bool
    {
        return array_key_exists($class, $this->collection);
    }

    /**
     * Locates entity class from trailing class name.
     * 
     * @param string $name
     * @return boolean|string
     */
    public function locate(string $name): ?string
    {
        if ($this->isExist($name)) {
            return $name;
        }

        $directories = Nishchay::getStructureProcessor()->getDirectories('entity');
        foreach ($directories as $namespace => $path) {
            $class = $namespace . '\\' . $name;
            if ($this->isExist($class)) {
                return $class;
            }
        }
        return null;
    }

    /**
     * Returns all registered entity classes.
     * 
     * @return array
     */
    public function get(): array
    {
        return $this->collection;
    }

    /**
     * Returns total number of defined entities in an application.
     */
    public function count(): int
    {
        return count($this->collection);
    }

}
