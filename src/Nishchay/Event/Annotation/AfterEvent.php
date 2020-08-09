<?php

namespace Nishchay\Event\Annotation;

use Nishchay\Annotation\BaseAnnotationDefinition;

/**
 * After Event Annotation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class AfterEvent extends BaseAnnotationDefinition
{

    /**
     * Order parameter.
     * 
     * @var array 
     */
    private $order = [];

    /**
     * Callback parameter.
     * 
     * @var string 
     */
    private $callback = false;

    /**
     * Once parameter.
     * 
     * @var boolean 
     */
    private $once = false;

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);
        $this->setter($parameter, 'parameter');
    }

    /**
     * Returns order in which event should be called.
     * 
     * @return  array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Returns callback to call if defined.
     * 
     * @return  array
     */
    public function getCallback()
    {
        if ($this->callback === false) {
            return false;
        }

        if (strpos($this->callback, '::') !== false) {
            $callback = explode('::', $this->callback);
        } else {
            $callback = [$this->class, $this->callback];
        }
        return $callback;
    }

    /**
     * Sets order in which event should be called.
     * 
     * @param   string|array        $order
     */
    protected function setOrder($order)
    {
        $this->order = (array) $order;
    }

    /**
     * Sets event callback.
     * 
     * @param array $callback
     */
    protected function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Returns TRUE if event is set to execute only once.
     * 
     * @return  boolean
     */
    public function getOnce()
    {
        return $this->once;
    }

    /**
     * Sets once flag for event execution.
     * 
     * @param   boolean   $once
     */
    protected function setOnce($once)
    {
        $this->once = (bool) $once;
    }

}
