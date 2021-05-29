<?php

namespace Nishchay\Event;

use Nishchay;
use ReflectionMethod;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Processor\Names;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Attributes\Event\EventConfig;

/**
 * Event Method annotation
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
     * Intended annotation.
     * 
     * @var \Nishchay\Event\Annotation\Method\Intended 
     */
    private $intended;

    /**
     * Fire annotation.
     * 
     * @var \Nishchay\Event\Annotation\Method\Fire 
     */
    private $fire;

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
     * Returns intended annotation.
     * 
     * @return \Nishchay\Event\Annotation\Method\Intended
     */
    public function getIntended()
    {
        return $this->intended;
    }

    /**
     * Returns file annotation.
     * 
     * @return \Nishchay\Event\Annotation\Method\Fire
     */
    public function getFire()
    {
        return $this->fire;
    }

    /**
     * Sets intended annotation.
     * 
     * @param   array   $intended
     */
    protected function setIntended($intended)
    {
        $this->intended = new Intended($this->class, $this->method, $intended);
    }

    /**
     * Sets fire annotation.
     * 
     * @param   array   $fire
     */
    protected function setFire($fire)
    {
        $this->fire = new Fire($this->class, $this->method, $fire);
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
