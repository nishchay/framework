<?php

namespace Nishchay\Data\Reflection;

use Nishchay\Attributes\Entity\Property\{
    Property,
    DataType
};
use Nishchay\Data\AbstractEntityStore;

/**
 * Data property class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DataProperty extends AbstractEntityStore
{

    /**
     * Name of property's class.
     * 
     * @var string 
     */
    private $class;

    /**
     * Name of property.
     * 
     * @var string 
     */
    private $name;

    /**
     * Instance of property attribute.
     * 
     * @var Property 
     */
    private $property;

    /**
     * 
     * @param   string  $class
     * @param   string  $name
     */
    public function __construct($class, $name)
    {
        $this->class = is_string($class) ? $class : get_class($class);
        $this->name = $name;
        $this->setProperty($this->entity($this->class)->getProperty($this->name));
    }

    /**
     * Sets property attribute for property.
     * 
     * @param Property $property
     */
    private function setProperty(Property $property)
    {
        $this->property = $property;
    }
    
    /**
     * Returns property attribute for property.erty.
     * 
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Returns name of property's class.
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Returns name of property.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns TRUE if property is identity.
     * 
     * @return boolean
     */
    public function isIdentity()
    {
        return $this->property->getIdentity();
    }

    /**
     * Returns TRUE if property is derived.
     * 
     * @return boolean
     */
    public function isDerived()
    {
        return $this->property->isDerived();
    }

    /**
     * Returns DataType attribute of property.
     * 
     * @return string
     */
    public function getDataType()
    {
        return $this->isDerived() ? null : $this->property->getDatatype()->getType();
    }

    /**
     * Returns TRUE if data type of property is primitive.
     * 
     * @return boolean
     */
    public function isPrimitiveType()
    {
        return $this->isDerived() ? false : in_array($this->getDataType(), DataType::PREDEFINED_TYPES);
    }

    /**
     * Returns TRUE if property data type is an class.
     * 
     * @return boolean
     */
    public function isObjectType()
    {
        return $this->isDerived() ? false : $this->isPrimitiveType() === false;
    }

    /**
     * Returns length limit of property.
     * 
     * @return double
     */
    public function getLengthLimit()
    {
        return $this->property->getDatatype()->getLength();
    }

}
