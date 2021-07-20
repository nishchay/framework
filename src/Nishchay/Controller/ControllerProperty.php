<?php

namespace Nishchay\Controller;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Nishchay\DI\DI;
use Nishchay\Service\Service;
use \Nishchay\Processor\FetchSingletonTrait;
use Nishchay\Attributes\Controller\Property\{
    Get,
    Post,
    Service as ServiceAttribute
};

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

    use FetchSingletonTrait;

    /**
     *
     * @var object 
     */
    private $instance;

    /**
     * 
     * @var ReflectionClass
     */
    private ReflectionClass $reflection;

    /**
     * Instances list.
     * 
     * @var object 
     */
    protected static $instances = [];

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

        $property->setAccessible(true);

        $attributes = $property->getAttributes();

        if (!empty($attributes)) {
            $attribute = current($attributes);

            switch ($attribute->getName()) {
                case Get::class:
                case Post::class:
                    $property->setValue($this->instance,
                            $this->getAttributeValue($property, $attribute));
                    break;
                case ServiceAttribute::class:
                    $property->setValue($this->instance, new Service());
                    break;
            }
        }

        $type = $property->getType()?->getName();

        if ($type !== null && class_exists($type)) {
            $property->setValue($this->instance,
                    $this->getInstance(DI::class)->create($type));
        }
    }

    /**
     * 
     * @param ReflectionProperty $property
     * @param ReflectionAttribute $attribute
     */
    private function getAttributeValue(ReflectionProperty $property,
            ReflectionAttribute $attribute)
    {
        $type = $property->getType()?->getName();
        $value = $attribute->newInstance()->getValue();
        if ($type === 'array') {
            $value = (array) $value;
        }

        return $value;
    }

}
