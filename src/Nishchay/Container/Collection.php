<?php

namespace Nishchay\Container;

use Nishchay;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Processor\AbstractCollection;
use Nishchay\Persistent\System;

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
     * Initialization
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Saves instance to route persistent file if application in live stage
     * and updates flag to store mode.
     * 
     * @return NULL
     */
    private function init()
    {
        if (Nishchay::isApplicationStageLive() && System::isPersisted('containers')) {
            $this->collection = System::getPersistent('containers');
        }
    }

    /**
     * Stores class into collection.
     * 
     * @param string $class
     */
    public function store(string $class)
    {
        $this->collection[$class] = new Container($class);
    }

    public function get(string $class)
    {
        if (array_key_exists($class, $this->collection) === false) {
            throw new NotSupportedException('Class [' . $class . '] is not container.', null, null, 934001);
        }

        return $this->collection[$class];
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
