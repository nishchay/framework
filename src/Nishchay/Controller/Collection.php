<?php

namespace Nishchay\Controller;

use Nishchay\Persistent\System as SystemPersistent;
use Nishchay\Processor\AbstractCollection;

/**
 * Controller Collection class stores all controller defined.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Collection extends AbstractCollection
{

    /**
     * Lists of controller.
     * 
     * @var array 
     */
    protected $collection = [];

    /**
     * Stores controller in collection.
     * 
     * @param string    $class
     * @param object    $object
     */
    public function store($class, $object, $context)
    {
        if ($context === false) {
            return;
        }

        $this->checkStoring();
        $this->collection[$class] = [
            'object' => $object,
            'context' => $context
        ];
    }

    /**
     * Returns all controller.
     * 
     * @return array
     */
    public function get()
    {
        return $this->collection;
    }

    /**
     * If controller list has been persisted to file we will fetch
     * controller list from it.
     * 
     * @return null
     */
    private function refactor()
    {
        if (!empty($this->collection)) {
            return;
        }

        if (SystemPersistent::isPersisted('controllers')) {
            $this->collection = SystemPersistent::getPersistent('controllers');
        }
    }

    /**
     * Returns given class.
     * 
     * @param   string      $class
     * @return  object
     */
    public function getClass($class)
    {
        $this->refactor();
        if (!array_key_exists($class, $this->collection)) {
            return false;
        }

        return $this->collection[$class]['object'];
    }

    /**
     * Returns method of class.
     * String should be class::method.
     * 
     * @param   string      $method
     * @return  boolean
     */
    public function getMethod($method)
    {
        if (strpos($method, '::') !== false) {
            list($class, $methodName) = explode('::', $method);
            if (($controller = $this->getClass($class)) !== false) {
                return $controller->getMethod($methodName);
            }
            return false;
        }
        return false;
    }

    /**
     * Returns context of given class.
     * 
     * @param   string      $class
     * @return  string
     */
    public function getContext($class)
    {
        $this->refactor();
        return $this->collection[$class]['context'];
    }

    /**
     * Returns total number of controllers defined in an application.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->collection);
    }

}
