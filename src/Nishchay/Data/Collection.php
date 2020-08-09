<?php

namespace Nishchay\Data;

use Nishchay\Processor\AbstractCollection;
use Nishchay\Persistent\System;

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
    public function register($class)
    {
        $this->checkStoring();
        $this->collection[$class] = false;
    }

    /**
     * Returns TRUE when passed class_name is registered as entity.
     * 
     * @param   string      $class
     * @return  boolean
     */
    public function isExist($class)
    {
        return array_key_exists($class, $this->collection);
    }

    /**
     * Returns all registered entity classes.
     * 
     * @return array
     */
    public function get()
    {
        return $this->collection;
    }

    /**
     * Returns total number of defined entities in an application.
     */
    public function count()
    {
        return count($this->collection);
    }

}
