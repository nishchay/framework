<?php

namespace Nishchay\Processor\Annotation;

use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Annotation\BaseAnnotationDefinition;

/**
 * ClassType annotation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ClassType extends BaseAnnotationDefinition
{

    /**
     * Type of class.
     * 
     * @var string
     */
    private $classtype;

    /**
     * Sets annotation value to this class.
     * 
     * @param type $class
     * @param type $annotation
     */
    public function __construct($class, $annotation)
    {
        parent::__construct($class, null);
        $this->setter($annotation);
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
     * @param array $classtype
     * @return $this
     * @throws InvalidAnnotationExecption
     */
    public function setClasstype($classtype)
    {
        if (count($classtype) !== 1 || !isset($classtype['type'])) {
            throw new InvalidAnnotationExecption('Invalid classType annotation.', $this->class, null, 925001);
        }
        $this->classtype = $classtype['type'];
        return $this;
    }

}
