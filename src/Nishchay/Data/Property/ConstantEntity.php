<?php

namespace Nishchay\Data\Property;

use Nishchay\Exception\ApplicationException;

/**
 * Description of ConstantEntity
 *
 * @author bpatel
 */
class ConstantEntity
{

    /**
     *
     * @var type 
     */
    private $entityClass;

    /**
     *
     * @var type 
     */
    private $entityProperties = [];

    /**
     * 
     * @param type $entityProperties
     * @param type $entityClass
     */
    public function __construct($entityProperties, $entityClass)
    {
        $this->entityClass = $entityClass;
        $this->entityProperties = $entityProperties;
    }

    /**
     * 
     * @param type $name
     * @return type
     * @throws ApplicationException
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->entityProperties)) {

            return $this->entityProperties[$name];
        }

        throw new ApplicationException('Property [' . $this->entityClass .
                '::' . $name . '] does not exists.', $this->entityClass, null, 911055);
    }

    /**
     * 
     * @param type $name
     * @param type $value
     * @throws ApplicationException
     */
    public function __set($name, $value)
    {
        throw new ApplicationException('Property [' . $this->entityClass .
                '::' . $name . '] can not be updated while in trigger.', $this->entityClass, null, 911056);
    }

    /**
     * Returns all properties.
     * 
     * @return type
     */
    public function __debugInfo()
    {
        return $this->entityProperties;
    }

}
