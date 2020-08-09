<?php

namespace Nishchay\Data\Annotation\Property;

use Nishchay;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\ApplicationException;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Data\Query;

/**
 * Relative annotation class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Relative extends BaseAnnotationDefinition
{

    /**
     * Relation type loose.
     */
    const LOOSE = 'loose';

    /**
     * Relation type perfect.
     */
    const PERFECT = 'perfect';

    /**
     * Name of property on which this annotation is defined.
     * 
     * @var stirng 
     */
    private $propertyName;

    /**
     * Relative to class.
     * 
     * @var string 
     */
    private $to;

    /**
     * Type of relation.
     * 
     * @var string 
     */
    private $type = '[<]';

    /**
     * Property name to which may be relative to.
     * 
     * @var string 
     */
    private $name = false;

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $parameter
     */
    public function __construct($class, $method, $property_name, $parameter)
    {
        parent::__construct($class, $method);
        $this->propertyName = $property_name;
        $sort = ['to', 'type'];
        $this->setter(ArrayUtility::customeKeySort($parameter, $sort),
                'parameter');
    }

    /**
     * 
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 
     * @param string $to
     */
    protected function setTo($to)
    {
        if (Nishchay::getEntityCollection()->isExist($to) === FALSE) {
            throw new ApplicationException('Relative class [' . $to . '] defiend for'
                    . ' property [' . $this->class . '::' . $this->propertyName
                    . '] does not exist.', $this->class, null, 911026);
        }
        $this->to = $to;
    }

    /**
     * 
     * @param string $type
     */
    protected function setType($type)
    {
        $type = strtolower($type);
        $allowed = [
            self::LOOSE => Query::LEFT_JOIN,
            self::PERFECT => Query::INNER_JOIN
        ];

        if (!array_key_exists($type, $allowed)) {
            throw new InvalidAnnotationExecption('Invalid relative type [' .
                    $type . '] for property [' . $this->class . '::' .
                    $this->propertyName . '].', $this->class, null, 911027);
        }

        $this->type = $allowed[$type];
    }

    /**
     * Returns name of property to which may be relative to.
     * 
     * @return string|boolean
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name to which property may be relative to.
     * 
     * @param type $name
     */
    protected function setName($name)
    {
        $this->name = $name;
    }

}
