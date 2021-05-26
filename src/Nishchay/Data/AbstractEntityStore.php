<?php

namespace Nishchay\Data;

use Nishchay;
use Exception;
use Nishchay\Exception\ApplicationException;
use AnnotationParser;
use ReflectionClass;
use Nishchay\Persistent\System;
use Nishchay\Data\Annotation\EntityClass;

/**
 * Description of Entity Store.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
abstract class AbstractEntityStore
{

    /**
     * Collections of entities.
     * 
     * @var array 
     */
    private static $collection = [];

    /**
     * 
     * @param type $class
     * @return \Nishchay\Data\Annotation\EntityClass
     * @throws Exception
     */
    protected function entity($class)
    {

        # Entities are process only once during each request
        # when it's first usage is made at any point. On next usage we fetch
        # entity from persisted entities.
        if (array_key_exists($class, self::$collection)) {
            return self::$collection[$class];
        }
        
        if ($this->isEntity($class) === FALSE) {
            throw new ApplicationException('Class [' . $class . '] is not registered entity.',
                            1, null, 911061);
        }

        return $this->instanciate($class);
    }

    /**
     * Returns true if $class is registered entity.
     * 
     * @param string $class
     * @return boolean
     */
    protected function isEntity($class)
    {
        return Nishchay::getEntityCollection()->isExist($class);
    }

    /**
     * Stores entity class instance into collection.
     * 
     * @param string $class
     * @return \Nishchay\Data\Annotation\EntityClass
     */
    private function instanciate($class)
    {
        if (($entity = $this->getFromPersistant($class))) {
            return $entity;
        }

        return $this->persist($this->getInstance($class)->resolveDependency());
    }

    /**
     * Persist entity instance.
     * 
     * @param EntityClass $entity
     * @return EntityClass
     */
    private function persist(EntityClass $entity)
    {
        System::setPersistent($entity->getClass(), $entity);
        $this->store($entity->getClass(), $entity);
        return $entity;
    }

    /**
     * Returns entity from persisted.
     * 
     * @param string $class
     * @return boolean|Object
     */
    private function getFromPersistant($class)
    {
        if (System::isPersisted($class)) {
            return $this->store($class, System::getPersistent($class));
        }
        return false;
    }

    /**
     * Returns instance of Entity class.
     * 
     * @param string $class
     * @return \Nishchay\Data\Annotation\EntityClass
     */
    private function getInstance($class)
    {
        $entity = new EntityClass($class, $this->getAttributes($class));
        return $this->store($class, $entity);
    }

    /**
     * Stores entity instance into collection.
     * 
     * @param string $class
     * @param Object $instnace
     * @return Object
     */
    private function store($class, $instnace)
    {
        return self::$collection[$class] = $instnace;
    }

    /**
     * Returns annotation of class.
     * 
     * @param string $class
     * @return array
     */
    private function getAttributes(string $class)
    {
        $reflection = new ReflectionClass($class);
        return $reflection->getAttributes();
    }

}
