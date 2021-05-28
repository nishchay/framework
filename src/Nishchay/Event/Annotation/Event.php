<?php

namespace Nishchay\Event\Annotation;

use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\InvalidAttributeException;
use ReflectionClass;
use ReflectionMethod;
use Nishchay\Event\Annotation\Method\Method as MethodAnnotation;
use Nishchay\Utility\Coding;

/**
 * Event Annotation of event type class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Event extends BaseAnnotationDefinition
{

    /**
     * Annotations.
     * 
     * @var array 
     */
    private $annotation;

    /**
     * Event annotation.
     * 
     * @var boolean 
     */
    private $event;

    /**
     * 
     * @param   string      $class
     * @param   array       $annotation
     */
    public function __construct($class, $annotation)
    {
        parent::__construct($class, null);
        $this->annotation = $annotation;
        $this->setter($annotation);
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
     * Sets event annotation to true.
     * 
     * @param   boolean                         $event
     * @throws  InvalidAnnotationExecption
     */
    protected function setEvent($event)
    {
        if ($event !== false) {
            throw new InvalidAnnotationExecption('Annotation [event] does not support'
                            . ' paramters.', $this->class, $this->method, 916006);
        }
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
                $attributes = $method->getAttributes();

                # Ignoring methods which does not have annotation on them.
                if (empty($attributes)) {
                    continue;
                }

                # Creating just instnace and it will parses event method and
                # stores it into event collection.
                new MethodAnnotation($this->class, $method->name, $attributes);
            } catch (Exception $ex) {
                throw new InvalidAttributeException($ex->getMessage(),
                                $this->class, $method->name, 916007);
            }
        }
    }

}
