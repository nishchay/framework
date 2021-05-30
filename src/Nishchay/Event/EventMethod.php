<?php

namespace Nishchay\Event;

use Nishchay;
use ReflectionMethod;
use Nishchay\Processor\Names;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Attributes\Event\EventConfig;

/**
 * Event Method attribute
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class EventMethod
{

    use AttributeTrait;

    /**
     * Is event fired or not.
     * 
     * @var boolean 
     */
    private $fired = false;

    /**
     * 
     * @var EventConfig
     */
    private $eventConfig;

    /**
     * 
     * @param   string  $class
     * @param   string  $method
     * @param   array   $attributes
     */
    public function __construct(ReflectionMethod $reflection)
    {
        $this->setClass($reflection->class)
                ->setMethod($reflection->name);

        $this->process($reflection);
    }

    /**
     * 
     * @param ReflectionMethod $reflection
     * @return type
     */
    public function process(ReflectionMethod $reflection)
    {
        $attribute = current($reflection->getAttributes(EventConfig::class));

        if ($attribute->getName() !== EventConfig::class) {
            return;
        }
        $this->eventConfig = $attribute->newInstance()
                ->setClass($this->class)
                ->setMethod($this->method)
                ->verify();
        $this->storeEvent();
    }

    /**
     * Stores event into collection.
     */
    public function storeEvent()
    {
        $type = $this->eventConfig->getType();
        if ($type !== Names::TYPE_GLOBAL) {
            return Nishchay::getEventCollection()
                            ->store($this, $this->eventConfig->getName());
        }
        Nishchay::getEventCollection()->storeGlobal($this);
    }

    /**
     * Returns TRUE if event already been fired.
     * 
     * @return  boolean
     */
    public function isFired()
    {
        return $this->fired;
    }

    /**
     * Marks event has been fired.
     */
    public function markFired()
    {
        $this->fired = true;
    }

}
