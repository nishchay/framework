<?php

namespace Nishchay\Data\Property;

use Nishchay\Exception\ApplicationException;
use Nishchay\Data\Annotation\Property\Relative;
use Nishchay\Data\Query;

/**
 * Join Table class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class ResolvedJoin
{

    /**
     * Hold type array.
     */
    const HOLD_TYPE_ARRAY = 'array';

    /**
     * Parent alias.
     * 
     * @var string
     */
    private $parentAlias;

    /**
     * Join rule as table.
     * 
     * @var array 
     */
    private $join;

    /**
     * Join rule as class.
     * 
     * @var array 
     */
    private $classJoin;

    /**
     * Property name.
     * 
     * @var array 
     */
    private $propertyName = [];

    /**
     * Hold type
     * 
     * @var string 
     */
    private $holdType;

    /**
     * Type of join.
     * 
     * @var string 
     */
    private $joinType;

    /**
     * Class of property.
     * 
     * @var string 
     */
    private $propertyClass;

    /**
     * Database connection name for join.
     * 
     * @var type 
     */
    private $joinConnection;

    /**
     * Group by for join.
     * 
     * @var string 
     */
    private $groupBy = false;

    /**
     * Returns alias for parent class.
     * 
     * @return type
     */
    public function getParentAlias()
    {
        return $this->parentAlias;
    }

    /**
     * Returns table join.
     * 
     * @return type
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * Returns name of properties which need to be fetched.
     * 
     * @return array
     */
    public function getPropertyNameToFetch()
    {
        return is_array($this->propertyName) ? $this->propertyName : [$this->propertyName];
    }

    /**
     * Return hold type. Join type.
     * 
     * @return type
     */
    public function getHoldType()
    {
        return $this->holdType;
    }

    /**
     * Sets parent alias for class.
     * 
     * @param type $parent_alias
     * @return \Nishchay\Data\Property\ResolvedJoin
     */
    public function setParentAlias($parent_alias)
    {
        $this->parentAlias = $parent_alias;
        return $this;
    }

    /**
     * Sets table join.
     * 
     * @param type $join
     * @return \Nishchay\Data\Property\ResolvedJoin
     */
    public function setJoin($join)
    {
        $this->join = $join;
        return $this;
    }

    /**
     * Sets properties which need to be fetched.
     * 
     * @param type $property_name
     * @return \Nishchay\Data\Property\ResolvedJoin
     */
    public function setPropertyNameToFetch($property_name)
    {
        $this->propertyName = $property_name;
        return $this;
    }

    /**
     * Sets hold type.
     * 
     * @param type $hold_type
     * @return \Nishchay\Data\Property\ResolvedJoin
     */
    public function setHoldType($hold_type)
    {
        $this->holdType = $hold_type;
        return $this;
    }

    /**
     * Add class for property.
     * 
     * @param type $propertyName
     * @param type $class
     */
    public function addPropertyClass($propertyName, $class)
    {
        $this->propertyClass[$propertyName] = $class;
    }

    /**
     * Returns classes of properties of single class if property name is passed.
     * 
     * @param type $propertyName
     * @return EntityClass
     */
    public function getPropertyClass($propertyName = null)
    {
        if ($propertyName === null) {
            return $this->propertyClass;
        }

        if (array_key_exists($propertyName, $this->propertyClass) !== false) {
            return $this->propertyClass[$propertyName];
        }

        throw new ApplicationException('No class found for property [' . $propertyName . ']', null, null, 911090);
    }

    /**
     * Returns JOIN connection.
     * 
     * @return type
     */
    public function getJoinConnection()
    {
        return $this->joinConnection;
    }

    /**
     * 
     * @param type $join_class
     * @return \Nishchay\Data\Property\ResolvedJoin
     */
    public function setJoinConnection($join_class)
    {
        $this->joinConnection = $join_class;
        return $this;
    }

    /**
     * 
     * @return type
     */
    public function getClassJoin()
    {
        return $this->classJoin;
    }

    /**
     * 
     * @param type $class_join
     * @return \Nishchay\Data\Property\ResolvedJoin
     */
    public function setClassJoin($class_join)
    {
        $this->classJoin = $class_join;
        return $this;
    }

    /**
     * Returns join  type either loose or perfect.
     * 
     * @return stirng
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    /**
     * Sets Join Type.
     * 
     * @param type $type
     */
    public function setJoinType($type)
    {
        if ($type === Relative::PERFECT || $type === Query::INNER_JOIN) {
            $this->joinType = Relative::PERFECT;
        } else {
            $this->joinType = Relative::LOOSE;
        }
        return $this;
    }

    /**
     * Sets group by.
     * 
     * @return boolean|array
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * Sets group by.
     * 
     * @param boolean|array $groupBy
     * @return $this
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

}
