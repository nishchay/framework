<?php

namespace Nishchay\Attributes;

use Nishchay\Exception\ApplicationException;
use \Nishchay\Utility\MethodInvokerTrait;

/**
 * Description of BaseAttribute
 *
 * @author bhavik
 */
trait AttributeTrait
{

    use MethodInvokerTrait;

    /**
     * class name where attribute was defined.
     * 
     * @var string
     */
    protected $class;

    /**
     * Method name where attribute was defined.
     * 
     * @var string
     */
    protected $method;

    /**
     * 
     * @param array $attributes
     */
    protected function processAttributes(array $attributes)
    {
        foreach ($attributes as $attribute) {
            $constantName = $attribute->getName() . '::NAME';
            if (!defined($constantName)) {
                continue;
            }
            $name = constant($constantName);
            $method = 'set' . ucfirst($name);
            if (!method_exists($this, $method)) {
                continue;
            }
            $this->invokeMethod([$this, $method], [$attribute->newInstance()]);
        }
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * 
     * @param type $name
     * @param type $arguments
     * @return type
     * @throws ApplicationException
     */
    public function __call($name, $arguments)
    {
        $property = lcfirst(substr($name, 3));
        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        throw new ApplicationException('Method [' . $name . '] does not exists.');
    }

}