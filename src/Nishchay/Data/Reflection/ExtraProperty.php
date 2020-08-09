<?php

namespace Nishchay\Data\Reflection;

use Nishchay\Data\EntityManager;

/**
 * Extra property class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ExtraProperty
{

    /**
     * Name of the Property.
     * 
     * @var string 
     */
    private $propertyName;

    /**
     * Data type of the property.
     * 
     * @var string 
     */
    private $type = 'mixed';

    /**
     * Length.
     * 
     * @var double 
     */
    private $length = 0;

    /**
     * Read only flag.
     * 
     * @var boolean 
     */
    private $readonly = false;

    /**
     * Required flag.
     * 
     * @var boolean 
     */
    private $required = false;

    /**
     * EntityManager for the extra property.
     * 
     * @var \Nishchay\Data\EntityManager 
     */
    private $entity;

    /**
     * 
     * @param string $propertyName
     */
    public function __construct($propertyName)
    {
        $this->propertyName = (string) $propertyName;
    }

    /**
     * Set Data Type.
     * 
     * @param type $data_type
     * @return \Nishchay\Data\Reflection\ExtraProperty
     */
    public function setDataType($data_type)
    {
        $this->type = (string) $data_type;
        return $this;
    }

    /**
     * Set Character Length.
     * 
     * @param int $length
     * @return \Nishchay\Data\Reflection\ExtraProperty
     */
    public function setLength($length)
    {
        $this->length = (int) $length;
        return $this;
    }

    /**
     * Make property read only or updateable.
     * Passing TRUE makes property read only.
     * 
     * @param boolean $flag
     * @return \Nishchay\Data\Reflection\ExtraProperty
     */
    public function setReadOnly($flag)
    {
        $this->readonly = (bool) $flag;
        return $this;
    }

    /**
     * Make property required or optional.
     * Passing TRUE makes property required.
     * 
     * @param boolean $flag
     * @return \Nishchay\Data\Reflection\ExtraProperty
     */
    public function setRequired($flag)
    {
        $this->required = (bool) $flag;
        return $this;
    }

    /**
     * Bind Extra Property to.
     * 
     * @param EntityManager $entity
     * @return \Nishchay\Data\Reflection\ExtraProperty
     */
    public function bindTo(EntityManager $entity)
    {
        $this->entity = $entity;
        return $this;
    }

}
