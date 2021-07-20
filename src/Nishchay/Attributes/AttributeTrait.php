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
     * @var type
     */
    protected $verified = false;

    /**
     * 
     * @param array $attributes
     */
    protected function processAttributes(array $attributes)
    {
        foreach ($attributes as $attribute) {
            $isReflection = $attribute instanceof \ReflectionAttribute;
            $constantName = ( $isReflection ? $attribute->getName() : $attribute::class) . '::NAME';
            if (!defined($constantName)) {
                continue;
            }
            $name = constant($constantName);
            $method = 'set' . ucfirst($name);
            if (!method_exists($this, $method)) {
                continue;
            }
            $instance = $isReflection ? $attribute->newInstance() : $attribute;
            $instance->setClass($this->class)
                    ->setMethod($this->method);
            $this->invokeMethod([$this, $method], [$instance]);

            if (method_exists($instance, 'verify')) {
                $instance->verify();
            }
        }
    }

    /**
     * 
     * @throws ApplicationException
     */
    public function verify()
    {
        if ($this->verified) {
            throw new ApplicationException(message: 'Attribute already been verified.',
                            code: 925048);
        }
        $this->verified = true;
    }

    /**
     * 
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * 
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * 
     * @param string|null $class
     * @return self
     */
    public function setClass(?string $class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * 
     * @param string|null $method
     * @return self
     */
    public function setMethod(?string $method): self
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

        throw new ApplicationException(message: 'Method [' . $name . '] does not exists.',
                        code: 925049);
    }

}
