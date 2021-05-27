<?php

namespace Nishchay\Data\Property\Join;

use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Exception\InvalidAnnotationExecption;
use Nishchay\Data\EntityClass;
use Nishchay\Data\AbstractEntityStore;

/**
 * Custom join class.
 * 
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class CustomJoin extends AbstractEntityStore
{

    /**
     * Entity class instance.
     * 
     * @var EntityClass 
     */
    private $entity;

    /**
     * Property name for which derived property is 
     * to be resolved.
     * 
     * @var string 
     */
    private $propertyName;

    /**
     * Resolved join.
     * 
     * @var array 
     */
    private $resolvedJoin = [];

    /**
     * Join class.
     * 
     * @var array 
     */
    private $joinClass = [];

    /**
     * Join  Alias.
     * 
     * @var array 
     */
    private $joinAlias = [];

    /**
     * Last class name of join.
     * 
     * @var string 
     */
    private $lastClass = '';

    /**
     * Returns last table name of join.
     * 
     * @var string 
     */
    private $lastAlias;

    /**
     * 
     * @param   EntityClass        $entity
     * @param   string                                      $propertyName
     */
    public function __construct($entity, $propertyName)
    {
        $this->entity = $entity;
        $this->propertyName = $propertyName;
        $this->process();
    }

    /**
     * Processes for generating join rule.
     * 
     * @throws ApplicationException
     */
    private function process()
    {
        $derived = $this->entity->getProperty($this->propertyName)->getDerived();
        $join = $derived->getJoin();

        # Iterating over each join statement rule and replacing entity class 
        # to their actual entity name.
        foreach ($join as $table => $joinCondition) {

            $rule = $this->getJoinRule($table);
            $this->lastClass = $rule[2];
            $this->lastAlias = $tableName = $rule[3];
            $entity = $this->entity($this->lastClass);

            if ($entity->getConnect() !== $this->entity->getConnect()) {
                throw new NotSupportedException('Each entity should be from'
                        . ' same database connection defined on derived property ['
                        . $this->entity->getClass() . '::' . $this->propertyName . '].', $this->entity->getClass(), null, 911050);
            }

            $this->joinAlias[$tableName] = $this->lastClass;
            $table = str_replace($this->lastClass, $entity->getEntity()->getName(), $table);
            $this->resolvedJoin[$table] = $joinCondition;
            $this->joinClass[$table] = $entity->getConnect();
        }
    }

    /**
     * Returns join rule.
     * 
     * @param   string                      $rule
     * @return  array
     * @throws  InvalidAnnotationExecption
     */
    private function getJoinRule($rule)
    {
        # Adding join type if missed.
        if (!preg_match('#^\[(\>\<|\<|\>)\](.*)#', $rule)) {
            $rule = "[<]$rule";
        }

        # Every statment must match below pattern.
        # [join_type]ClassName(table_alias). 
        # If any of this is missing it is considered as invalid.
        if (preg_match('#^\[(\>\<|\<|\>)\](.*)\((.*)\)$#', $rule, $tmatch)) {
            return $tmatch;
        }
        throw new InvalidAnnotationExecption('Invalid join rule defined on property '
                . '[' . $this->entity->getClass() . '::' . $this->propertyName . '].', $this->entity->getClass(), null, 911051);
    }

    /**
     * Returns resolved join.
     * 
     * @return array
     */
    public function getResolvedJoin()
    {
        return $this->resolvedJoin;
    }

    /**
     * Returns join alias.
     * 
     * @return array
     */
    public function getJoinAlias()
    {
        return $this->joinAlias;
    }

    /**
     * Returns TRUE if alias exist.
     * 
     * @param   string      $name
     * @return  boolean
     */
    public function isAliasExist($name)
    {
        return array_key_exists($name, $this->joinAlias);
    }

    /**
     * Returns class name of given alias.
     * 
     * @param   string      $name
     * @return  string
     */
    public function getClassOfAlias($name)
    {
        return $this->joinAlias[$name];
    }

    /**
     * Returns join class.
     * 
     * @return array
     */
    public function getJoinClass()
    {
        return $this->joinClass;
    }

    /**
     * Returns last class of join.
     * 
     * @return string
     */
    public function getLastClass()
    {
        return $this->lastClass;
    }

    /**
     * Returns last alias of join.
     * 
     * @return string
     */
    public function getLastAlias()
    {
        return $this->lastAlias;
    }

}
