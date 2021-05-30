<?php

namespace Nishchay\Processor\Annotation;

use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Attributes\AttributeTrait;

/**
 * ClassType attribute class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ClassType
{

    use AttributeTrait;

    /**
     * Type of class.
     * 
     * @var string
     */
    private $classType;

    /**
     * Sets annotation value to this class.
     * 
     * @param type $class
     * @param type $attributes
     */
    public function __construct($class, $attributes)
    {
        $this->setClass($class);
        $this->processAttributes($attributes);
    }

    /**
     * Returns class type.
     * 
     * @return string
     */
    public function getClasstype()
    {
        return $this->classtype;
    }

    /**
     * Sets class type.
     * 
     * @param array $classType
     * @return $this
     */
    public function setClasstype($classType)
    {
        $this->classType = $classType;
    }

}
