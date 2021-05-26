<?php

namespace Nishchay\Data\Reflection;

use Nishchay\Exception\ApplicationException;
use ReflectionProperty;
use Nishchay\Utility\Coding;
use Nishchay\Attributes\Entity\Property\{
    Property,
    DataType
};
use Nishchay\Data\AbstractEntityStore;

/**
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DataClass extends AbstractEntityStore
{

    /**
     *
     * @var string 
     */
    private $name;

    /**
     *
     * @var Nishchay\Data\Annotation\EntityClass 
     */
    private $entityClass;

    /**
     * 
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = is_object($name) ? get_class($name) : $name;
        $this->entityClass = $this->entity($this->name);
    }

    /**
     * Returns property name of identity property.
     * 
     * @return type
     */
    public function getIdentity()
    {
        return $this->entityClass->getIdentity();
    }

    /**
     * Returns table name of entity class.
     * 
     * @return string
     */
    public function getTableName()
    {
        return $this->entityClass->getEntity()->getName();
    }

    /**
     * Returns the name of class.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 
     * @param   string                                  $name
     * @return  Nishchay\Data\Reflection\DataProperty
     */
    public function getProperty($name)
    {
        return new DataProperty($this->name, $name);
    }

    /**
     * 
     * @return array
     */
    public function getProperties()
    {
        $properties = [];
        foreach ($this->entityClass->getProperties() as $property) {
            $properties[$property] = new DataProperty($this->name, $property);
        }
        return $properties;
    }

    /**
     * 
     * @return array
     */
    public function getTriggers()
    {
        return [
            'before' => $this->entityClass->getBeforechange(),
            'after' => $this->entityClass->getAfterchange()
        ];
    }

    /**
     * 
     * @param \Nishchay\Data\Reflection\ExtraProperty        $extra
     */
    public function addExtraProperty(ExtraProperty $extra)
    {
        $config = Coding::getAsArray($extra);
        $propertyName = $config['propertyName'];
        $entity = $config['entity'];

        # Checking if property is bound to enitty or not.
        if ($entity === null) {
            throw new ApplicationException('You must bind property to'
                            . ' entity before adding extra property.', null,
                            null, 911059);
        }

        # New property name should not be same as class's existing properties
        # including extra property and extraProperty.
        if ($propertyName === Property::EXTRA_PROPERTY ||
                $entity->isPropertyExist($propertyName) ||
                $entity->isExtraPropertyExists($propertyName)) {
            throw new ApplicationException('Property [' . $entity->getEntityName() . '::' .
                            $propertyName . '] already exists.', null, null,
                            911060);
        }

        # Remove config which are not required for data type.
        unset($config['propertyName']);
        unset($config['entity']);
        $dataType = new DataType($entity->getEntityName(), null, $propertyName,
                $config);

        # Adding property to entity.
        # We first creating property reflection so that we can 
        # fetch existing value to update it by adding extra property. 
        # At the end we will update object property with new value.
        $reflection = new ReflectionProperty($entity, Property::EXTRA_PROPERTY);
        $reflection->setAccessible(TRUE);
        $value = $reflection->getValue($entity);

        # Adding property to entity with default value that's NULL.
        $value[$propertyName] = ['rule' => $dataType, 'value' => null];
        $reflection->setValue($entity, $value);
    }

}
