<?php

namespace Nishchay\Data\Property\Join;

use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Data\AbstractEntityStore;
use Nishchay\Data\EntityClass;
use Nishchay\Attributes\Entity\Property\{
    Property,
    Derived
};
use Nishchay\Data\Query;

/**
 * From Type Derived Property.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class FromProperty extends AbstractEntityStore
{

    /**
     * Entity class instance.
     * 
     * @var EntityClass 
     */
    private $entity;

    /**
     * Join to create from property.
     * 
     * @var array 
     */
    private $fromProperty;

    /**
     * Property name.
     * 
     * @var string 
     */
    private $propertyName;

    /**
     * Last entity name of join.
     * 
     * @var string 
     */
    private $lastEntity;

    /**
     * Returns last alias of join.
     * 
     * @var string 
     */
    private $lastAlias;

    /**
     * Resolved join.
     * 
     * @var array 
     */
    private $resolvedJoin;

    /**
     * Join rule using entity class name.
     * 
     * @var type 
     */
    private $classJoin;

    /**
     * Returns class used in join.
     * 
     * @var array 
     */
    private $joinConnection = [];

    /**
     * Join type.
     * 
     * @var string 
     */
    private $joinType;

    /**
     * Extended join flag.
     * 
     * @var boolean 
     */
    private $extendedJoin = false;

    /**
     * Group by for join.
     * 
     * @var array 
     */
    private $group = false;

    /**
     * 
     * @param EntityClass  $entity
     */
    public function __construct(EntityClass $entity, string $propertyName,
            Derived $derived)
    {
        $this->entity = $entity;
        $this->fromProperty = explode('.', $derived->getFrom());
        $this->propertyName = $propertyName;
        $this->process($derived);
    }

    /**
     * Iterates over each property and builds join rule.
     * 
     * @param Derived     $derived
     */
    public function process(Derived $derived)
    {
        $parent = $this->entity;
        foreach ($this->fromProperty as $key => $from) {
            $entity = $parent;
            $relativeProperty = $this->getRelativeProperty($entity, $from);
            $reverse = false;
            if ($relativeProperty->getClass() === $entity->getClass()) {
                $relative = $this->getRelative($relativeProperty);
                $parent = $this->entity($relative->getTo());
                $joinType = $relative->getType();
            } else {
                $reverse = true;
                $parent = $this->entity($from);
                $joinType = $derived->getType() === false ? Query::LEFT_JOIN : false;
            }

            if ($this->entity->getConnect() !== $parent->getConnect()) {
                $this->extendedJoin = true;
            }

            $parentName = $parent->getEntity()->getName();
            $this->lastEntity = $parent->getEntity()->getClass();

            $this->joinType = $type = $derived->getType() !== false ? $derived->getType() : $joinType;
            $previousAlias = $key === 0 ? $entity->getEntity()->getName() : $this->lastAlias;
            $this->lastAlias = $parentName . '_' . $this->propertyName;

            if ($reverse) {
                $joinRule = [
                    $relativeProperty->getPropertyName() => $previousAlias . '.' .
                    $entity->getIdentity()
                ];
            } else {
                $relativeName = $relative->getName() === null ?
                        $parent->getIdentity() : $relative->getName();
                $joinRule = [
                    $relativeName => $previousAlias . '.' .
                    $relativeProperty->getPropertyName()
                ];
            }
            $this->resolvedJoin["{$type}{$parentName}(" . $this->lastAlias . ")"] = $joinRule;
            $this->classJoin["{$type}" .
                    $parent->getEntity()->getClass() .
                    "(" . $this->lastAlias . ")"] = $joinRule;
            $this->joinConnection[$this->lastAlias] = $parent->getConnect();
            $this->setGroup($derived->getGroup());
        }

        if (count(array_flip($this->joinConnection)) > 1) {
            throw new NotSupportedException('You should not derive property'
                            . ' using more than one database connection for [' .
                            $this->entity->getClass() . '::' . $this->propertyName . '].',
                            $this->entity->getClass(), null, 911052);
        }
    }

    /**
     * Sets group by clause for join.
     * 
     * @param type $group
     * @return type
     */
    private function setGroup($group)
    {
        if ($group === false) {
            return;
        }

        foreach ($group as $key => $value) {
            $group[$key] = "{$this->lastAlias}.{$value}";
        }
        $this->group = $group;
    }

    /**
     * Returns group by clause for join.
     * 
     * @param type $group
     * @return type
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Returns last entity of join.
     * 
     * @return      string
     */
    public function getLastEntity()
    {
        return $this->lastEntity;
    }

    /**
     * Returns last alias of join.
     * 
     * @return      string
     */
    public function getLastAlias()
    {
        return $this->lastAlias;
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
     * Returns join rule by class name.
     * 
     * @return type
     */
    public function getClassJoin()
    {
        return $this->classJoin;
    }

    /**
     * Returns join class.
     * 
     * @return array
     */
    public function getJoinConnection()
    {
        return $this->joinConnection;
    }

    /**
     * Returns self property name.
     * 
     * @return string
     */
    public function getSelfProperty()
    {
        return $this->fromProperty[0];
    }

    /**
     * Returns join type.
     * 
     * @return string
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    /**
     * Returns true if join consists of multiple connection.
     * 
     * @return boolean
     */
    public function isExtendedJoin()
    {
        return $this->extendedJoin;
    }

    /**
     * Returns relative property.
     * 
     * @param   EntityClass            $entity
     * @param   string                                          $from
     * @return  Relative
     * @throws  ApplicationException
     */
    private function getRelativeProperty(EntityClass $entity, $from)
    {
        if (($property = $entity->getProperty($from)) instanceof Property) {
            return $property;
        }

        # Does not found above, now considering that $from is class and we will
        # now look of class's has relative property to $enitty class's identity.
        if ($this->isEntity($from) && ($property = $this->entity($from)
                ->getRelativeOf($entity->getClass())) instanceof Property) {
            return $property;
        }

        throw new ApplicationException('[' . $from . '] should be valid'
                        . ' property of self class or it should be entity for derived property'
                        . ' [' . $this->entity->getClass() . '::' . $this->propertyName . '].',
                        $this->entity->getClass(), null, 911053);
    }

    /**
     * Returns relative attribute if it is valid.
     * 
     * @param   Property             $self
     * @return  Relative
     * @throws  ApplicationException
     */
    private function getRelative($self)
    {
        $relative = $self->getRelative();
        if ($relative !== null) {
            return $relative;
        }

        throw new ApplicationException('Property [' . $self->getClass() . '::' . $self->getPropertyName() .
                        '] is not relative to any class.', $self->getClass(),
                        null, 911054);
    }

}
