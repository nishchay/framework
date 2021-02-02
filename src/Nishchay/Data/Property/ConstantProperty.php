<?php

namespace Nishchay\Data\Property;

use Nishchay\Exception\ApplicationException;

/**
 * Constant Property class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ConstantProperty
{

    /**
     * List of properties.
     * 
     * @var array 
     */
    private $constantProperties = [];

    /**
     * Class name of the properties.
     * 
     * @var string 
     */
    private $belongsToProperty = false;

    /**
     * 
     * @param type $properties
     * @param type $belong
     */
    public function __construct($properties, $belong)
    {
        $this->constantProperties = (array) $properties;
        $this->belongsToProperty = $belong;
    }

    /**
     * Returns property value.
     * 
     * @param type $name
     * @return type
     * @throws ApplicationException
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->constantProperties)) {
            return $this->constantProperties[$name];
        }

        throw new ApplicationException('Property [' . $name . '] of derived'
                        . ' property [' . $this->belongsToProperty . '] does not'
                        . ' exists.', null, null, 911057);
    }

    /**
     * Prevents update to property value.
     * 
     * @param type $name
     * @param type $value
     * @throws ApplicationException
     */
    public function __set($name, $value)
    {
        throw new ApplicationException('Property [' . $this->belongsToProperty . '] is not updatable.', null, null, 911058);
    }

    /**
     * Returns all properties.
     * 
     * @return type
     */
    public function __debugInfo()
    {
        return $this->constantProperties;
    }

    /**
     * Returns TRUE if given key exists.
     * 
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->constantProperties);
    }

}
