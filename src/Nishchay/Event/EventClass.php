<?php

namespace Nishchay\Event;

use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Exception\ApplicationException;
use ReflectionClass;
use ReflectionMethod;
use Nishchay\Utility\Coding;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Attributes\Event\Event;

/**
 * Event attribute of event type class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class EventClass
{

    use AttributeTrait;

    /**
     * Event attribute.
     * 
     * @var boolean 
     */
    private $event;

    /**
     * 
     * @param   string      $class
     * @param   array       $attributes
     */
    public function __construct($class, $attributes)
    {
        $this->setClass($class);
        $this->processAttributes($attributes);

        if ($this->event === null) {
            throw new ApplicationException('[' . $class . '] must be event.',
                            $class);
        }

        $this->iterateMethods();
    }

    /**
     * Returns TRUE.
     * 
     * @return boolean
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Sets event attribute to true.
     * 
     * @param   Event $event
     */
    protected function setEvent(Event $event)
    {
        $this->event = true;
    }

    /**
     * Iterate over all methods to find events.
     * 
     * @throws  InvalidAttributeException
     */
    protected function iterateMethods()
    {
        $reflection = new ReflectionClass($this->class);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

            # Ignoring invalid methods.
            if (Coding::isIgnorable($method, $this->class)) {
                continue;
            }

            try {
                # Ignoring methods which does not have attribute on them.
                if (empty($method->getAttributes())) {
                    continue;
                }

                # Creating just instnace and it will parses event method and
                # stores it into event collection.
                new EventMethod($method);
            } catch (Exception $ex) {
                throw new InvalidAttributeException($ex->getMessage(),
                                $this->class, $method->name, 916007);
            }
        }
    }

}
