<?php

namespace Nishchay\Event;

use Nishchay;
use Nishchay\Persistent\System as SystemPersistent;
use Nishchay\Processor\AbstractCollection;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Event\Annotation\Method\Fire;
use Nishchay\Processor\Names;

/**
 * Event Collection class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Collection extends AbstractCollection
{

    const EVENT = 'event';

    /**
     * Registered event class.
     * 
     * @var array 
     */
    private $eventClass = [];

    /**
     * Event collection.
     * 
     * @var array 
     */
    private $collection;

    /**
     * Event persistent flag.
     * 
     * @var array 
     */
    private $persisted = FALSE;

    /**
     * Initialization.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize event collection.
     * 
     * @return NULL
     */
    private function init()
    {
        if (SystemPersistent::isPersisted(static::EVENT)) {
            $persistent = SystemPersistent::getPersistent(static::EVENT);
            $this->collection = $persistent['collection'];
            $this->eventClass = $persistent['classes'];
        } else {
            foreach ([Names::TYPE_GLOBAL, Names::TYPE_CONTEXT, Names::TYPE_SCOPE] as $type) {
                $this->collection[$type] = [
                    Fire::BEFORE => [], Fire::AFTER => []
                ];
            }
        }
    }

    /**
     * Persist event if not persisted and application is LIVE.
     * 
     * @return boolean
     */
    public function persist()
    {
        if ($this->persisted) {
            return false;
        }

        if (Nishchay::isApplicationStageLive()) {
            SystemPersistent::setPersistent(static::EVENT, ['collection' => $this->collection, 'classes' => $this->eventClass]);
        }
        $this->persisted = true;
    }

    /**
     * Stores scope or context event.
     * 
     * @param   \Nishchay\Event\Annotation\Method\Method         $annotation
     * @param   string                                          $value
     * @param   array                                           $callback
     */
    public function store($annotation, $value)
    {
        if ($this->isExist($annotation->getClass())) {
            $this->collection[$annotation->getIntended()->getType()]
                    [$annotation->getFire()->getWhen()][$value][] = $annotation;
        }
    }

    /**
     * Stores global event.
     * 
     * @param   string      $when
     * @param   \Nishchay\Event\Annotation\Method\Method      $annotation
     */
    public function storeGlobal($annotation)
    {
        if ($this->isExist($annotation->getClass())) {
            $this->collection[Names::TYPE_GLOBAL][$annotation->getFire()->getWhen()][] = $annotation;
        }
    }

    /**
     * Returns TRUE if class is registered event class.
     * 
     * @param   string      $class
     * @return  boolean
     */
    public function isExist($class)
    {
        return array_key_exists($class, $this->eventClass);
    }

    /**
     * Returns events list.
     * 
     * @param   string      $when
     * @param   string      $context
     * @param   string      $scope
     * @return  array
     */
    public function getEvents($when, $context, $scope, $order)
    {
        $declared = [
            Names::TYPE_GLOBAL => $this->collection[Names::TYPE_GLOBAL][$when],
            Names::TYPE_CONTEXT => $this->getContextEvent($context, $when),
            Names::TYPE_SCOPE => $this->getScopeEvent($scope, $when)
        ];
        $sorted = ArrayUtility::customeKeySort($when === Fire::BEFORE ?
                        $declared : array_reverse($declared), $order);
        $events = [];
        foreach ($sorted as $value) {
            $events = array_merge($events, $value);
        }
        return $events;
    }

    /**
     * Returns events defined for scope.
     * 
     * @param   array      $scope
     * @param   string      $when
     * @return  array
     */
    public function getScopeEvent($scope, $when)
    {
        $events = [];
        if ($scope === false) {
            return $events;
        }
        foreach ($scope as $name) {
            if (isset($this->collection [Names::TYPE_SCOPE][$when][$name])) {
                $events = array_merge($events, $this->collection [Names::TYPE_SCOPE][$when][$name]);
            }
        }
        return $events;
    }

    /**
     * Returns events defined for context.
     * 
     * @param   string      $name
     * @param   string      $when
     * @return  array
     */
    public function getContextEvent($name, $when)
    {
        return isset($this->collection [Names::TYPE_CONTEXT][$when][$name]) ?
                $this->collection [Names::TYPE_CONTEXT][$when][$name] : [];
    }

    /**
     * Returns global event.
     * 
     * @param type $when
     * @return type
     */
    public function getGlobalEvent($when)
    {
        return $this->collection[Names::TYPE_GLOBAL][$when];
    }

    /**
     * Register Event class.
     * 
     * @param   string      $class
     */
    public function register($class)
    {
        $this->checkStoring();
        $this->eventClass[$class] = true;
    }

    /**
     * Returns total number of defined events in an application.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * Returns Event collection.
     */
    public function get()
    {
        return $this->collection;
    }

}
