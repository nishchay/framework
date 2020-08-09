<?php

namespace Nishchay\Event\Annotation\Method;

use Nishchay;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\ApplicationException;
use Nishchay\Processor\Names;

/**
 * Event Method annotation
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Method extends BaseAnnotationDefinition
{

    /**
     * Intended annotation.
     * 
     * @var \Nishchay\Event\Annotation\Method\Intended 
     */
    private $intended = false;

    /**
     * Fire annotation.
     * 
     * @var \Nishchay\Event\Annotation\Method\Fire 
     */
    private $fire = false;

    /**
     * Is event fired or not.
     * 
     * @var boolean 
     */
    private $fired = false;

    /**
     * 
     * @param   string  $class
     * @param   string  $method
     * @param   array   $annotation
     */
    public function __construct($class, $method, $annotation)
    {
        parent::__construct($class, $method);

        #Annotation array is not used in many places so no need to store.
        $this->setter($annotation);
        if ($this->intended === false || $this->fire === false) {
            throw new ApplicationException('Event method requires both '
                    . 'intended and fire annotation.', $this->class, $this->method, 916005);
        }
        $this->storeEvent();
    }

    /**
     * Stores event into collection.
     */
    public function storeEvent()
    {
        $type = $this->intended->getType();
        if ($type !== Names::TYPE_GLOBAL) {
            return Nishchay::getEventCollection()
                            ->store($this, $this->intended->getName());
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
