<?php

namespace Nishchay\Data\Annotation\Trigger;

use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\InvalidAnnotationParameterException;

/**
 * After change annotation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class AfterChange extends BaseAnnotationDefinition
{

    /**
     * Callback method name.
     * 
     * @var array 
     */
    private $callback;

    /**
     * Priority of the trigger.
     * 
     * @var int 
     */
    private $priority = 0;

    /**
     * Trigger for how much types of modification.
     * 
     * @var array 
     */
    private $for = ['insert', 'update', 'remove'];

    /**
     * 
     * @param type $class
     * @param type $method
     * @param type $parameter
     */
    public function __construct($class, $method, $parameter)
    {
        parent::__construct($class, $method);

        # Callback is not required if this annotation is defined on method of
        # entity class.
        if ($method !== null) {
            unset($parameter['callback']);
        }

        $this->setter($parameter, 'parameter');
    }

    /**
     * Sets callback method.
     * 
     * @return string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Returns priority of the trigger.
     * 
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Returns trigger defined for what kind of modification.
     * 
     * @return array
     */
    public function getFor()
    {
        return $this->for;
    }

    /**
     * Sets callback method.
     * 
     * @param array $callback
     */
    protected function setCallback($callback)
    {
        # Ignoring callback parameter while annotation defined on method. This
        # method will be considered as callback.
        if ($this->method !== null) {
            $callback = $this->method;
        }

        # Now we will prepend class name if only method has been set as 
        # callback. In this case, we consider class on which annotation
        # defined.
        if (strpos($callback, '::') === false) {
            $callback = "{$this->class}::{$callback}";
        }

        $this->callback = explode('::', $callback);
    }

    /**
     * Returns callback class.
     * 
     * @return string
     */
    public function getCallbackClass()
    {
        return $this->callback[0];
    }

    /**
     * Returns callback method.
     * 
     * @return string
     */
    public function getCallbackMethod()
    {
        return $this->callback[1];
    }

    /**
     * Set priority of the trigger.
     *  
     * @param int $priority
     */
    protected function setPriority($priority)
    {
        $this->priority = (int) $priority;
    }

    /**
     * Sets trigger for how many types of modification.
     * 
     * @param string|array $for
     */
    public function setFor($for)
    {
        $for = (array) $for;

        foreach ($for as $type) {
            if (!in_array($type, $this->for)) {
                throw new InvalidAnnotationParameterException('Invalid value'
                        . ' for parameter [for] for annotation'
                        . ' [AfterChange].', $this->class, $this->method, 911029);
            }
        }

        $this->for = $for;
    }

    /**
     * Returns TRUE if this trigger is for insert.
     * 
     * @return boolean
     */
    public function isForInsert()
    {
        return in_array('insert', $this->for);
    }

    /**
     * Returns TRUE if this trigger is for update.
     * 
     * @return boolean
     */
    public function isForUpdate()
    {
        return in_array('update', $this->for);
    }

    /**
     * Returns TRUE if this trigger is for remove.
     * 
     * @return boolean
     */
    public function isForRemove()
    {
        return in_array('remove', $this->for);
    }

}
