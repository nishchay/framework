<?php

namespace Nishchay\Annotation;

use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Base annotation definition provide support for actual annotation. 
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class BaseAnnotationDefinition
{

    use MethodInvokerTrait;

    /**
     * Class name in which annnotation is defined.
     * 
     * @var string 
     */
    protected $class;

    /**
     * Method name on which annotation is defined.
     * Value is NULL when annotation defined on classs.
     * 
     * @var string 
     */
    protected $method = NULL;

    /**
     * Initializes class and method with value where they are defined.
     * 
     * @param string $class
     * @param string $method
     */
    public function __construct($class, $method)
    {
        $this->class = $class;
        $this->method = $method;
    }

    /**
     * Returns the name of class where annotation is defined.
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Returns the name of method where annotation is defined. Returns NULL 
     * when defined on class. $method parameter added as optional to make 
     * extending class implement other things.
     * 
     * @return string
     */
    public function getMethod($method = NULL)
    {
        return $this->method;
    }

    /**
     * Setter method to set value of annotation or parameter of the annotation.
     * Helps ensures that annotation or annotation parameter following 
     * prototype of the annotation. If the setter method of the same does not 
     * found, throws exception as either invalid annotation or annotation 
     * parameter depends on the value of $type passed.
     * 
     * @param   string                          $property
     * @throws  InvalidAnnotationParameterException
     */
    protected function setter($property, $type = '')
    {
        if (!is_array($property)) {
            throw new InvalidAnnotationExecption('Parameter missing.',
                            $this->class, $this->method);
        }

        foreach ($property as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (!method_exists($this, $method)) {
                $message = 'Invalid annotation ' . $type . ' ' . $key;

                # This method can be called from annotation class and annotation
                # parameter class too.
                if ($type !== '') {
                    throw new InvalidAnnotationParameterException($message,
                                    $this->class, $this->method);
                } else {
                    throw new InvalidAnnotationExecption($message, $this->class,
                                    $this->method);
                }
            }

            call_user_func([$this, $method], $value);
        }
    }

    /**
     * 
     * @param array $annotations
     */
    protected function processAttributes(array $annotations)
    {
        foreach ($annotations as $attribute) {
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

}
