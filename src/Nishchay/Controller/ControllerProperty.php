<?php

namespace Nishchay\Controller;

use AnnotationParser;
use stdClass;
use ReflectionClass;
use ReflectionProperty;
use Nishchay\DI\DI;
use Nishchay\Service\Service;

/**
 * Process properties defined in controller class to autobind values.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ControllerProperty
{

    /**
     *
     * @var object 
     */
    private $instance;

    /**
     *
     * @var object 
     */
    private $reflection;

    /**
     * 
     * @param object $instance
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->reflection = new ReflectionClass($this->instance);
        $this->process();
    }

    /**
     * 
     */
    private function process()
    {
        foreach ($this->reflection->getProperties() as $property) {
            $this->processProperty($property);
        }
    }

    /**
     * 
     * @param ReflectionProperty $property
     */
    private function processProperty(ReflectionProperty $property)
    {
        $annotations = AnnotationParser::getAnnotations($property->getDocComment());
        if (empty($annotations)) {
            return false;
        }
        list($annotation, $value) = [key($annotations), current($annotations)];
        $property->setAccessible(true);
        $calling = new DI(new stdClass());
        switch ($annotation) {
            case 'bind':
                $property->setValue($this->instance, $calling->create($value, true));
            case 'service':
                $property->setValue($this->instance, new Service());
            default:
                break;
        }
    }

}
