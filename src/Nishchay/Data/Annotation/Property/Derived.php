<?php

namespace Nishchay\Data\Annotation\Property;

use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Data\Query;

/**
 * Derived Annotation class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Derived extends BaseAnnotationDefinition
{

    /**
     * Property name on which annoation is defined.
     * 
     * @var string 
     */
    private $propertyName = false;

    /**
     * JOIN config as defined in annotation parameter.
     * 
     * @var array 
     */
    private $join = false;

    /**
     * Property value to be retrieved from porperty or class.
     * 
     * @var string 
     */
    private $from = false;

    /**
     * Callback function name.
     * 
     * @var string 
     */
    private $callback = false;

    /**
     * Derived type. can be
     * callback,join_from,join.
     * 
     * @var string 
     */
    private $derivedType;

    /**
     * Properties to be fetched to set.
     * 
     * @var string 
     */
    private $property = false;

    /**
     * Data type of derived value.
     * 
     * @var \Nishchay\Data\Annotation\Property\DataType 
     */
    private $dataType;

    /**
     * Type of relation for join.
     * Can be loose or perfect.
     * 
     * @var string 
     */
    private $type = false;

    /**
     * Type of record to hold by property.
     * 
     * @var string 
     */
    private $hold = 'single';

    /**
     * Sets group by clause.
     * 
     * @var string 
     */
    private $group = false;

    /**
     * Initialization.
     * 
     * @param   string  $class
     * @param   string  $method
     * @param   string  $propertyName
     * @param   array   $parameter
     * @throws  \Nishchay\Exception\InvalidAnnotationExecption
     */
    public function __construct($class, $method, $propertyName, $parameter)
    {
        parent::__construct($class, $method);
        $this->propertyName = $propertyName;
        $priority = ['from', 'join', 'callback'];
        $common = $common = array_intersect(array_keys($parameter), $priority);
        if (count($common) > 1) {
            throw new InvalidAnnotationExecption('Property can only be derived'
                    . ' using one of these parameter [' . implode(',', $priority) . ']'
                    . ' but found [' . implode(',', $common) . '] for property'
                    . ' [' . $this->class . '::' . $this->propertyName . '].', $this->class, null, 911016);
        }

        $this->setter(ArrayUtility::customeKeySort($parameter, $priority), 'parameter');
    }

    /**
     * Returns property name on which annotation is defined.
     * 
     * @return stirng
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Returns JOIN config.
     * 
     * @return type
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * Return from value of derived annotation.
     * 
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Returns callback name.
     * 
     * @return type
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Returns type of join.
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets join rule.
     * 
     * @param array $callback
     */
    protected function setJoin($callback)
    {
        $this->derivedType = 'join';
        $class = $this->class;
        if (method_exists($class, $callback) === false) {
            throw new InvalidAnnotationParameterException('Join callback [' .
                    $class . '::' . $callback . '] does exist for property [' .
                    $this->class . '::' . $this->propertyName . '].', $this->class, null, 911017);
        }

        $this->join = call_user_func([new $class, $callback]);
    }

    /**
     * Sets from value.
     * 
     * @param string $from
     */
    protected function setFrom($from)
    {
        $this->derivedType = 'join_from';
        $this->from = $from;
    }

    /**
     * Returns true if property is deriving from property which is relative
     * to another entity.
     * 
     * @return tybooleanpe
     */
    public function isFrom()
    {
        return $this->derivedType === 'join_from';
    }

    /**
     * Returns true if property is derived using callback.
     * 
     * @return boolean
     */
    public function isCallback()
    {
        return $this->derivedType === 'callback';
    }

    /**
     * Returns true if property is derived using custom join.
     * 
     * @return boolean
     */
    public function isJoin()
    {
        return $this->derivedType === 'join';
    }

    /**
     * Sets callback.
     * 
     * @param string $callback
     */
    protected function setCallback($callback)
    {
        if (!method_exists($this->class, $callback)) {
            throw new InvalidAnnotationParameterException('Callback [' .
                    $callback . '] for derived property [' . $this->class .
                    '::' . $this->propertyName . ' ] does not exist.', $this->class, null, 911018);
        }
        $this->derivedType = 'callback';
        $this->callback = $callback;
    }

    /**
     * Returns type of derived.
     * 
     * @return string
     */
    public function getDerivedType()
    {
        return $this->derivedType;
    }

    /**
     * Returns properties.
     * 
     * @return array
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Sets properties.
     * 
     * @param string $property
     */
    protected function setProperty($property)
    {
        $this->property = (array) $property;
    }

    /**
     * Sets type of JOIN.
     * 
     * @param   string      $type
     */
    protected function setType($type)
    {
        $type = strtolower($type);
        $allowed = [
            Relative::LOOSE => Query::LEFT_JOIN,
            Relative::PERFECT => Query::INNER_JOIN
        ];
        if (!array_key_exists($type, $allowed)) {
            throw new InvalidAnnotationExecption('Invalid relation type ['
                    . $type . '] for derived property [' . $this->class . '::' .
                    $this->propertyName . '].', $this->class, null, 911019);
        }

        $this->type = $allowed[$type];
    }

    /**
     * Registers data type of deriving property.
     * 
     * @param string $propertyName
     * @param string $dataType
     * @return boolean
     */
    public function registerDataType($propertyName, $dataType)
    {
        if ($propertyName === null) {
            $this->dataType = $dataType;
            return true;
        }
        $this->dataType[$propertyName] = $dataType;
        return true;
    }

    /**
     * Returns data type of deriving property.
     * 
     * @return \Nishchay\Data\Annotation\Property\DataType
     */
    public function getDataType($propertyName = null)
    {
        return $propertyName === null ?
                $this->dataType : $this->dataType[$propertyName];
    }

    /**
     * Returns number of record to be hold by property.
     * Can be single or multiple.
     * 
     * @return string
     */
    public function getHold()
    {
        return $this->hold;
    }

    /**
     * Sets hold type of record.
     * Can be single or multiple.
     * 
     * @param string $hold
     */
    protected function setHold($hold)
    {
        $allowedHold = [
            'single' => 'string',
            'multiple' => 'array'
        ];

        if (!array_key_exists($hold, $allowedHold)) {
            throw new InvalidAnnotationParameterException('Invalid hold type'
                    . ' for property [' . $this->class . '::' .
                    $this->propertyName . '].', $this->class, null, 911020);
        }
        $this->hold = $allowedHold[$hold];
    }

    /**
     * Return group parameter value.
     * 
     * @return array
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Sets group parameter value.
     * 
     * @param type $group
     */
    protected function setGroup($group)
    {
        $this->group = (array) $group;
    }

}
