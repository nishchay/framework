<?php

namespace Nishchay\Data;

use Nishchay;
use Nishchay\Exception\ApplicationException;
use Nishchay\Data\Query;
use Nishchay\Data\EntityManager;
use Nishchay\Data\Property\ResolvedJoin;
use Nishchay\Utility\Coding;

/**
 * Entity Query class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class EntityQuery extends AbstractEntityStore
{

    /**
     * Query Builder.
     * 
     * @var \Nishchay\Data\Query 
     */
    private $query;

    /**
     * Entities mappings.
     * 
     * @var array 
     */
    private $entityMapping = [];

    /**
     * Main entity class to be fetched from.
     * 
     * @var string
     */
    private $mainEntity;

    /**
     * Alias of main entity class to be fetched from.
     * 
     * @var string 
     */
    private $mainEntityAlias;

    /**
     *
     * @var array 
     */
    private $unfetchable = [];

    /**
     * Entities which should be returned as response.
     * 
     * @var array 
     */
    private $returnableEntity = [];

    /**
     * Deriving properties to be returned.
     * 
     * @var array 
     */
    private $derivingProperties = [];

    /**
     * Properties with value.
     * 
     * @var array 
     */
    private $propertyWithValues = [];

    /**
     * Whether to fetch lazy properties.
     * 
     * @var bool
     */
    private $lazy = false;

    /**
     * 
     * @param   string      $connection
     */
    public function __construct($connection = null)
    {
        $this->query = new Query($connection);
    }

    /**
     * Sets entity mapping to table.
     * 
     * @param   string          $alias
     * @param   string          $class
     * @throws  \Exception
     */
    public function setEntityMapping($alias, $class)
    {
        $this->isEntityExist($class) && $this->entityMapping[$alias] = $class;
    }

    /**
     * Returns true if given class is  an registered  entity.
     * 
     * @param   string          $class
     * @throws  \Exception
     */
    private function isEntityExist($class)
    {
        if (Nishchay::getEntityCollection()->isExist($class) === false) {
            throw new ApplicationException('Class [' . $class . '] is not registered entity.', null, null, 911075);
        }
        return true;
    }

    /**
     * Sets table to be fetched from.
     * 
     * @param   string      $class
     * @param   string      $alias
     */
    public function setTable($class, $alias = null)
    {
        return $this->setEntity($class, $alias);
    }

    /**
     * Set main entity from which record need to be fetched from.
     * 
     * @param string $class
     * @param string $alias
     * @return $this
     */
    public function setEntity($class, $alias = null)
    {
        $this->mainEntity = $class;
        $this->mainEntityAlias = $alias ?? Coding::getClassBaseName($class);
        $this->query->setTable($this->getTableName($class), $this->mainEntityAlias);
        $this->setEntityMapping($this->mainEntityAlias, $class);
        return $this;
    }

    /**
     * Returns table name associated with entity.
     * 
     * @param   string      $class
     * @return  string
     */
    private function getTableName($class)
    {
        return $this->entity($class)
                        ->getEntity()->getName();
    }

    /**
     * Sets conditions.
     * 
     * @param type $column
     * @param type $value
     * @return \Nishchay\Data\EntityQuery
     */
    public function setCondition($column, $value = null)
    {
        $this->query->setCondition($column, $value);
        return $this;
    }

    /**
     * Adds join.
     * 
     * @param array $join
     * @return \Nishchay\Data\EntityQuery
     */
    public function addJoin($join)
    {
        foreach ($join as $key => $value) {
            list($type, $class, $alias) = $this->getJoinRule($key);
            $this->setEntityMapping($alias, $class);
            $tableName = $this->getTableName($class);

            # Removing existing rule as we have manipulated existing join rule
            # and we want them to be replaced with manipulated.
            unset($join[$key]);
            $join[str_replace($class, $tableName, $key)] = $value;
        }
        $this->query->addJoin($join);
        return $this;
    }

    /**
     * Finds and sets column to be selected for object property.
     * 
     * 
     * @param type $alias
     */
    private function setObjectProperty($alias)
    {
        # We will add all properties from class belongs to property_name 
        # to query builder.
        $class = $this->getMappedClass($alias);
        $this->returnableEntity[$alias] = $class;
        $table = $this->getTableName($class);
        $columns = [];


        # We will set properties which are belongs to $propertyName's relative
        # class only. Not the property which are relative to another class or 
        # derived property.
        $entity = $this->entity($class);
        foreach ($entity->getNoJoinSelect($this->unfetchable, $this->query) as $colAlias => $toSelect) {
            if (array_key_exists($alias, $this->unfetchable) &&
                    in_array($toSelect, $this->unfetchable[$alias])) {
                continue;
            }
            $columns[$colAlias] = str_replace($table . '.', $alias . '.', $toSelect);
        }

        $this->query->setColumn($columns);
        return true;
    }

    /**
     * 
     * @param   array                           $properties
     * @return  \Nishchay\Data\EntityQuery
     */
    public function setProperty($properties)
    {
        foreach ((array) $properties as $key => $name) {
            $assignTo = null;

            # When key is not int, it means that caller wants to assign property
            # to provided entity only. In this case key becomes property and
            # name will be entity to which given property should be assigned.
            if (!is_int($key)) {
                $assignTo = $name;
                $name = $key;
            }

            # If property to be selected is alias name of class, we will 
            # fetch all property from class belongs to alias.
            if (strpos($name, '.') === false && $this->setObjectProperty($name)) {
                continue;
            }
            $this->query->setColumn([$name]);

            list($class, $propertyName) = explode('.', $name);
            if (is_array($assignTo) && $this->setNotToFetch($assignTo, $class, $propertyName)) {
                continue;
            }

            $this->returnableEntity[$class] = $this->getMappedClass($class);
        }
        return $this;
    }

    /**
     * 
     * @param type $property
     * @return $this
     */
    public function setDerivedProperty($property)
    {
        if (strpos($property, '.') === false) {
            $property = $this->mainEntityAlias . '.' . $property;
        }
        list($alias, $propertyName) = explode('.', $property);
        $entity = $this->entity($this->getMappedClass($alias));
        $property = $entity->getProperty($propertyName);
        if ($property->isDerived() === false) {
            throw new ApplicationException('[' . $propertyName . ']'
                    . ' is not derived property.', 1);
        }

        $join = $entity->getJoinTable($propertyName);

        if ($join->getHoldType() === ResolvedJoin::HOLD_TYPE_ARRAY) {
            throw new ApplicationException('Hold type array for entity'
                    . ' query is yet to develop');
        }

        if ($property->getDerived()->getProperty() === false) {
            throw new ApplicationException('Deriving whole entity for entity'
                    . ' query is yet to develop');
        }

        $joins = $join->getJoin();
        $mainTable = $entity->getEntity()->getName();
        foreach ($joins as $table => $joinRule) {
            foreach ($joinRule as $leftOperand => $rightOperand) {
                $joins[$table][$this->getReplacedAlias($mainTable, $leftOperand)] = $this->getReplacedAlias($mainTable, $rightOperand);
            }
        }
        $this->query->addJoin($joins);
        $properties = [];
        foreach ($join->getPropertyNameToFetch() as $name) {
            $properties[$propertyName . '_' . $name] = $join->getParentAlias() . '.' . $name;
        }
        $this->derivingProperties[] = $alias . '.' . $propertyName;
        $this->query->setColumn($properties);
        return $this;
    }

    /**
     * 
     * @param type $values
     * @return $this
     * @throws ApplicationException
     */
    public function setPropertyWithValue($values)
    {
        if (!empty($this->derivingProperties) && !empty($this->returnableEntity)) {
            throw new ApplicationException('Can not set property with value'
                    . ' when entity query is already prepared to fetch.', 1, null, 911076);
        }
        $entity = $this->entity($this->mainEntity);
        foreach ((array) $values as $name => $value) {

            # Finding if property exists in Main entity class.
            if (($property = $entity->getProperty($name)) === false) {
                throw new ApplicationException('Property to update [' .
                        $this->mainEntity . '::' . $name . '] does not exist.', 1, null, 911077);
            }

            # Disallowing static and derived property to be updated
            if ($property->isDerived() || $property->isStatic()) {
                throw new ApplicationException('Can not update derived or'
                        . ' static property[' . $this->mainEntity . '::' . $name . '].', 1, null, 911078);
            }

            # Disallowing read only property to be updated
            if ($property->getDatatype()->getReadonly()) {
                throw new ApplicationException('Can not update read only'
                        . ' property[' . $this->mainEntity . '::' . $name . '].', 1, null, 911079);
            }

            $this->propertyWithValues[$name] = $value;
        }

        return $this;
    }

    /**
     * Returns property with values.
     * 
     * @return array
     */
    public function getPropertyWithValue()
    {
        return $this->propertyWithValues;
    }

    /**
     * Updates entity properties and returns number of rows updated.
     * 
     * @return int
     */
    public function update()
    {
        $manager = new EntityManager($this->mainEntity);
        $updated = $manager->updateByEntityQuery($this);
        $this->reset();
        return $updated;
    }

    /**
     * Removes entity records.
     * 
     * @return int
     */
    public function remove()
    {
        return $this->query->remove();
    }

    /**
     * Replaces table name with alias of main entity.
     * 
     * @param string $table
     * @param string $name
     * @return string
     */
    private function getReplacedAlias($table, $name)
    {
        return str_replace($table, $this->mainEntityAlias, $name);
    }

    /**
     * Returns list of derived properties which need to be assigned.
     * 
     * @return array
     */
    public function getDerivingProperties()
    {
        return $this->derivingProperties;
    }

    /**
     * 
     * @param   string                                      $alias
     * @return  string
     * @throws  \Nishchay\Exception\ApplicationException
     */
    private function getMappedClass($alias)
    {
        if (array_key_exists($alias, $this->entityMapping)) {
            return $this->entityMapping[$alias];
        }
        throw new ApplicationException('Mapping not found for class alias [' . $alias . '].', 1, null, 911080);
    }

    /**
     * 
     * @return type
     */
    public function getReturnableEntity()
    {
        return $this->returnableEntity;
    }

    /**
     * Returns join rule.
     * 
     * @param   string                      $rule
     * @return  array
     * @throws  \Nishchay\Exception\ApplicationException
     */
    private function getJoinRule($rule)
    {
        # Adding join type if missed.
        if (!preg_match('#^\[(\>\<|\<|\>)\](.*)#', $rule)) {
            $rule = Query::LEFT_JOIN . $rule;
        }

        # Every statment must match below pattern.
        # [join_type]ClassName(table_alias). 
        # If any of this is missing it is considered as invalid.
        if (preg_match('#^\[(\>\<|\<|\>)\](.*)\((.*)\)$#', $rule, $match)) {
            # What has been which is at first index is not required.
            array_shift($match);
            return $match;
        }
        throw new ApplicationException('Invalid join rule [' . $rule . ']. Missing alias.', 1, null, 911081);
    }

    /**
     * Sets limit.
     * 
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function setLimit($limit, $offset)
    {
        $this->query->setLimit($limit, $offset);
        return $this;
    }

    /**
     * Sets order by.
     * 
     * @param   string|array                $orderBy
     * @return  \Nishchay\Data\EntityQuery
     */
    public function setOrderBy($orderBy)
    {
        $this->query->setOrderBy($orderBy);
        return $this;
    }

    /**
     * 
     * @param type $groupBy
     * @return $this
     */
    public function setGroupBy($groupBy)
    {
        $this->query->setGroupBy($groupBy);
        return $this;
    }

    /**
     * Binds value.
     * 
     * @param mixed $value
     * @return string
     */
    public function bindValue($value)
    {
        return $this->query->bindValue($value);
    }

    /**
     * Quotes column.
     * 
     * @param type $name
     * @return type
     */
    public function quote($name)
    {
        return $this->query->quote($name);
    }

    /**
     * Returns Query builder.
     * 
     * @return \Nishchay\Data\Query
     */
    public function getQueryBuilder()
    {
        return clone $this->query;
    }

    /**
     * Remove property to be set while setting property from
     * database.
     * 
     * @param   string|array  $propertyName
     */
    private function setNotToFetch($assignTo, $classAlias, $propertyName)
    {
        # Below we will find entities which are not present in $assign and
        # then we will mark property_name to be not to set for found entities.
        foreach (array_diff(array_keys($this->entityMapping), $assignTo) as $notToFetch) {
            $this->setUnfetchable("{$notToFetch}.{$propertyName}");
        }

        return !in_array($classAlias, $assignTo);
    }

    /**
     * Sets property not to fetch.
     * 
     * @param type $properties
     */
    public function setUnfetchable($properties)
    {
        if (is_string($properties)) {
            $properties = [$properties];
        }

        foreach ($properties as $property) {
            list($alias, $name) = explode('.', $property);
            $this->unfetchable[$alias][$name] = $name;
        }
        return $this;
    }

    /**
     * Sets Cache.
     * Pass $key = true if want to cache key to be auto created from executing query.
     * Passing $key = string will use the same key.
     * 
     * @param boolean|string $key
     * @param int $expiration
     * @return $this
     */
    public function setCache($key = true, $expiration = 0)
    {
        $this->query->setCache($key, $expiration);
        return $this;
    }

    /**
     * Returns all properties which are set to not to fetch for given alias.
     * 
     * @param   string      $name
     * @return  array
     */
    public function getUnFetchable($name)
    {
        return isset($this->unfetchable[$name]) ? $this->unfetchable[$name] : [];
    }

    /**
     * Executes query and returns record.
     * 
     * @return DataIterator
     */
    public function get()
    {
        if (empty($this->returnableEntity)) {
            throw new ApplicationException('Property to fetch is not set.', 1, null, 911082);
        }
        $manager = new EntityManager($this->mainEntity);
        if ($this->getLazy()) {
            $manager->enableLazy(true);
        }
        $result = $manager->fetchByEntityQuery($this);
        $this->reset();
        return $result;
    }

    /**
     * Executes query and returns first record from result.
     * 
     * @return \Nishchay\Data\EntityManager
     */
    public function getOne()
    {
        $this->query->setLimit(1);
        $row = $this->get()->current();
        return $row ?? false;
    }

    /**
     * Resets property to their default value.
     */
    private function reset()
    {
        $this->entityMapping = [];
        $this->mainEntity = null;
        $this->mainEntityAlias = null;
        $this->unfetchable = [];
        $this->returnableEntity = [];
        $this->derivingProperties = [];
        $this->propertyWithValues = [];
    }

    /**
     * Returns true if lazy property needs to be fetched.
     * 
     * @return bool
     */
    public function getLazy(): bool
    {
        return $this->lazy;
    }

    /**
     * Set true if lazy property need to fetched.
     * Works only when properties mentioned in select clause are belongs to single entity. 
     * 
     * @param bool $lazy
     * @return $this
     */
    public function setLazy(bool $lazy)
    {
        $this->lazy = $lazy;
        return $this;
    }

}
