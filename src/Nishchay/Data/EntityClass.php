<?php

namespace Nishchay\Data;

use Nishchay;
use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Nishchay\Data\AbstractEntityStore;
use Nishchay\Data\Connection\Connection;
use Nishchay\Data\EntityMethod;
use Nishchay\Attributes\Entity\Property\{
    Derived,
    Relative,
    Property as DataProperty
};
use Nishchay\Attributes\Entity\Event\{
    AfterChange,
    BeforeChange
};
use Nishchay\Data\Property\ResolvedJoin;
use Nishchay\Data\Property\Join\{
    FromProperty,
    CustomJoin
};
use Nishchay\Data\Meta\MetaTable;
use Nishchay\Data\DatabaseManager;
use Nishchay\Data\Query;
use Nishchay\Utility\Coding;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\Security\Encrypt\EncryptTrait;
use Nishchay\Utility\StringUtility;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Attributes\Entity\Entity;
use Nishchay\Attributes\Entity\Connect;

/**
 * Entity Class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class EntityClass extends AbstractEntityStore
{

    use MethodInvokerTrait,
        EncryptTrait,
        AttributeTrait;

    /**
     * Class name of entity.
     * 
     * @var string 
     */
    protected $class;

    /**
     * Entity attribute.
     * 
     * @var bool
     */
    private $entity = true;

    /**
     * Connect attribute.
     * 
     * @var string 
     */
    private $connect = false;

    /**
     * After change trigger attribute.
     * 
     * @var array
     */
    private $afterChange = [];

    /**
     * Before change trigger attribute.
     * 
     * @var array
     */
    private $beforeChange = [];

    /**
     *
     * @var ReflectionClass 
     */
    private $reflection;

    /**
     * All properties which has attribute defined within entity class.
     * 
     * @var array 
     */
    private $properties = [];

    /**
     * Static properties.
     * 
     * @var array 
     */
    private $staticProperties = [];

    /**
     *
     * @var array 
     */
    private $propertyRule = [];

    /**
     *
     * @var array 
     */
    private $propertyTypes = [
        'fetch' => [],
        'join' => [],
        'joinFrom' => [],
        'callback' => [],
        'derivedProperty' => [],
    ];

    /**
     * No join select fields.
     * 
     * @var array 
     */
    private $nojoinSelect = NULL;

    /**
     * Join select fields.
     * 
     * @var array 
     */
    private $joinSelect = NULL;

    /**
     * Identity property.
     * 
     * @var type 
     */
    private $identity = false;

    /**
     * Join tables.
     * 
     * @var array 
     */
    private $joinTable = [];

    /**
     * Extended join types.
     * 
     * @var array 
     */
    private $extendedJoin = [];

    /**
     * Flag for dependency resolved or not.
     * 
     * @var boolean 
     */
    private $dependencyResolved = false;

    /**
     * Flag for entity table column for extra property refactored or not.
     * 
     * @var boolean 
     */
    private $refactored = false;

    /**
     * Flag for static data table refactored or not.
     * 
     * @var boolean 
     */
    private static $staticRefactored = false;

    /**
     * Flag for static value fetched from database or not.
     * 
     * @var boolean 
     */
    private $staticValueFetched = false;

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $attributes
     */
    public function __construct($class, $attributes)
    {
        $this->class = $class;
        $this->processAttributes($attributes);
        $this->init();
    }

    /**
     * Returns name of enitty class to which this enitty is for.
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * 
     * @param EntityClass  $entity
     */
    private function refactor()
    {
        $this->refactorCoulumn()
                ->refactoreStaticTable()
                ->updateStaticProperties();
    }

    /**
     * Refactors table extra column property. It will create if doest not 
     * exist in table. It will not refactor if Disabled from configuration.
     * 
     * @return self
     */
    private function refactorCoulumn()
    {
        # If there are no properties in entity class then we don't add
        # extraProperty. This kind entity contains only static property which
        # does not requires its own table. Adding extraProperty create new table
        # if it does not exists.
        if (empty($this->properties)) {
            return $this;
        }

        if ($this->canColumnRefactor() === false ||
                $this->refactored === true) {
            return $this;
        }

        $this->refactored = true;
        $tableName = $this->getEntity()->getName();
        if (!(new MetaTable($tableName, $this->getConnect()))
                        ->isColumnExist(DataProperty::EXTRA_PROPERTY)) {
            (new DatabaseManager($this->getConnect()))
                    ->setTableName($tableName)
                    ->setColumn(DataProperty::EXTRA_PROPERTY, 'TEXT')
                    ->execute();
        }
        return $this;
    }

    /**
     * Returns column refactor flag value from database configuration.
     * 
     * @return  boolean
     */
    private function canColumnRefactor()
    {
        $refactoring = Nishchay::getSetting('database.global.refactoring');
        return is_bool($refactoring) ? $refactoring : false;
    }

    /**
     * Returns static table refactor flag from database configuration.
     * 
     * @return boolean
     */
    private function canStaticTableRefactor()
    {
        $staticRefactoring = Nishchay::getSetting('database.global.staticRefactoring');
        return is_bool($staticRefactoring) ? $staticRefactoring : false;
    }

    /**
     * Refactors static table into database.
     * It will create table if does not exist.
     * It will not refacor if
     * 1. static refactoring is disabled in configuration.
     * 2. static refactoring is NULL and column refactoring is disabled.
     * 
     * @return type
     */
    private function refactoreStaticTable()
    {
        $staticRefactoring = $this->canStaticTableRefactor();

        if (self::$staticRefactored === true ||
                $staticRefactoring === false ||
                ($staticRefactoring === null &&
                $this->canColumnRefactor() === false)
        ) {
            return $this;
        }

        self::$staticRefactored = true;
        $dbManager = new DatabaseManager($this->getConnect());
        $dbManager->setTableName(Entity::STATIC_TABLE_NAME);
        if ($dbManager->isTableExist() === true) {
            return $this;
        }

        $dbManager->setColumn('entityClass', ['VARCHAR' => 255])
                ->setColumn('propertyName', ['VARCHAR' => 100])
                ->setColumn('data', 'TEXT')
                ->execute();
        return $this;
    }

    /**
     * Sets values of all static properties of class by fetching from
     * static data table.
     *  
     */
    private function updateStaticProperties()
    {
        foreach (array_keys($this->getStaticProperties()) as $name) {

            # Fetching property
            $property = $this->getProperty($name);

            # Fetching value of property.
            $value = $this->getStaticValue($name);

            # Reflecting changes
            $property->updateStaticPropertyValue($value === null ?
                            null : $property->applySetter($value));
        }
    }

    /**
     * Returns value of static property from static data table.
     * 
     * @param   string          $name
     * @return  string
     */
    public function getStaticValue($name)
    {
        if ($this->staticValueFetched) {
            return array_key_exists($name, $this->staticProperties) ?
                    $this->staticProperties[$name] : null;
        }

        $this->fetchStaticValues();
        return $this->getStaticValue($name);
    }

    /**
     * Fetches static data from static data table and sets to registry. 
     */
    private function fetchStaticValues()
    {
        $this->staticValueFetched = true;

        # Preparing query
        $query = new Query($this->connect);
        $records = $query->setTable(Entity::STATIC_TABLE_NAME)
                ->setCondition([
                    'entityClass' => $this->class,
                    'propertyName[+]' => array_keys($this->getStaticProperties())
                ])
                ->get();
        foreach ($records as $row) {
            $property = $this->getProperty($row->propertyName);
            $value = $property->getValue($row->data);
            $this->staticProperties[$row->propertyName] = $value;
        }
    }

    /**
     * Setter method to set value of attribute.
     * 
     * @param   array                          $property
     * @throws  InvalidAttributeException
     */
    protected function setter($property, $type = '')
    {
        foreach ($property as $key => $value) {
            $method = 'set' . ucfirst($key);
            if ($this->isCallbackExist([$this, $method]) === false) {
                throw new InvalidAttributeException('Invalid attribute [' .
                                $type . ' ' . $key . '].', $this->class, null,
                                911034);
            }

            $this->invokeMethod([$this, $method], [$value]);
        }
    }

    /**
     * Returns entity attribute.
     * 
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Returns connect attribute.
     * 
     * @return string
     */
    public function getConnect()
    {
        return $this->connect;
    }

    /**
     * Returns Base query.
     * 
     * @return \Nishchay\Data\Query
     */
    public function getQuery()
    {
        $query = new Query($this->getConnect());
        $query->setTable($this->getEntity()->getName());
        return $query;
    }

    /**
     * Sets entity attribute.
     * 
     * @param array $entity
     */
    protected function setEntity(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Sets connect attribute.
     * 
     * @param string $connect
     */
    protected function setConnect(Connect $connect)
    {
        $this->connect = $connect->getName();
    }

    /**
     * Sets property.
     * 
     * @param   string  $name
     * @return  DataProperty
     */
    public function getProperty($name)
    {
        return array_key_exists($name, $this->propertyRule) ? $this->propertyRule[$name] : false;
    }

    /**
     * Returns property instance of class identity.
     * 
     * @return DataProperty
     */
    public function getIdentityProperty()
    {
        return $this->identity !== false ?
                $this->getProperty($this->identity) : false;
    }

    /**
     * Returns all non static properties.
     * 
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Returns all static properties.
     * 
     * @return array
     */
    public function getStaticProperties()
    {
        return $this->staticProperties;
    }

    /**
     * 
     * @return array
     */
    public function getFetchableProperties()
    {
        return $this->propertyTypes['fetch'];
    }

    /**
     * 
     * @return array
     */
    public function getJoinProperties()
    {
        return $this->propertyTypes['join'];
    }

    /**
     * 
     * @return array
     */
    public function getJoinFromProperties()
    {
        return $this->propertyTypes['joinFrom'];
    }

    /**
     * 
     * @return array
     */
    public function getCallbackProperties()
    {
        return $this->propertyTypes['callback'];
    }

    /**
     * 
     * @return type
     */
    public function getDerivedProperties()
    {
        return $this->propertyTypes['derivedProperty'];
    }

    public function getDerivedProperty($name)
    {
        if (array_key_exists($name, $this->propertyTypes['derivedProperty']) === false) {
            throw new ApplicationException('Derived property [' . $this->class
                            . '::' . $name . '] is does not exist.',
                            $this->class, null, 911035);
        }
        return $this->propertyTypes['derivedProperty'][$name];
    }

    /**
     * Returns true if atleast one derived property exist.
     * 
     * @return type
     */
    public function isDerivedPropertyExist()
    {
        return count($this->propertyTypes['derivedProperty']) > 0;
    }

    /**
     * Returns property only if it was defined to set via callback.
     * 
     * @param   string      $name
     * @return  string
     */
    public function getCallbackProperty($name)
    {
        return array_key_exists($name, $this->propertyTypes['callback']) ?
                $this->propertyTypes['callback'][$name] : false;
    }

    /**
     * Returns name of identity property.
     * 
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * 
     * @param   array       $unfetchable
     * @return  string
     */
    public function getJoinSelect($unfetchable = [], $query = null)
    {
        $this->preparePropertiesToSelect($unfetchable, $query, true);
        return array_diff_key($this->joinSelect, $unfetchable);
    }

    /**
     * 
     * @param   array       $unfetchable
     * @return  array
     */
    public function getNoJoinSelect($unfetchable = [], $query = null)
    {
        $this->preparePropertiesToSelect($unfetchable, $query, false);
        return array_diff_key($this->nojoinSelect, $unfetchable);
    }

    /**
     * 
     * @param   object  $name
     * @param   array   $parameter
     * @return  mixed
     */
    public function callMethod($object, $name, $parameter = [])
    {
        $reflection = new ReflectionMethod($this->class, $name);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs($object, $parameter);
    }

    /**
     * Adds JOIN table information.
     * 
     * @param   string      $name
     * @param   \Nishchay\Data\Property\ResolvedJoin      $join
     */
    protected function addJoinTable($name, $join)
    {
        $this->joinTable[$name] = $join;
    }

    /**
     * Adds extended Join table information.
     * 
     * @param   string      $name
     * @param   array       $join
     */
    protected function addExtendedJoin($name, $join)
    {
        $this->extendedJoin[$name] = $join;
    }

    /**
     * Returns JOIN column details.
     * 
     * @param       string      $name
     * @return      array
     */
    public function getJoinColumnDetail($name)
    {
        return isset($this->joinTable) ? $this->joinTable[$name] : false;
    }

    /**
     * 
     * @return \Nishchay\Data\Property\ResolvedJoin
     */
    public function getJoinTable($propertyName = NULL)
    {
        return $propertyName === NULL ? $this->joinTable :
                (isset($this->joinTable[$propertyName]) ?
                $this->joinTable[$propertyName] : false);
    }

    /**
     * 
     * @param   stirng                              $column
     * @return  \Nishchay\Data\Property\ResolvedJoin
     */
    public function getExtendedTable($column = NULL)
    {
        return $column === NULL ?
                $this->extendedJoin : $this->extendedJoin[$column];
    }

    /**
     * Initializes Enity class.
     * 
     */
    private function init()
    {
        # Letting developer be to free from declaring default 
        # connnection on entity. This makes connect attribute optional.
        if ($this->connect === false) {
            $this->connect = Connection::getDefaultConnectionName();
        }

        # Processing entity properties.
        foreach ($this->getReflectionClass()
                ->getProperties() as $property) {
            $this->processProperty($property);
        }

        # There should be atleast one property in entity class.
        if (empty($this->properties) && empty($this->staticProperties)) {
            throw new NotSupportedException('Entity class [' . $this->class . '] requires atleast'
                            . ' one property.', $this->class, null, 911036);
        }

        # Iterating over each method to find events for the entity.
        foreach ($this->getReflectionClass()->getMethods() as $method) {
            if (Coding::isIgnorable($method, $this->class)) {
                continue;
            }

            $attributes = $method->getAttributes();

            if (empty($attributes)) {
                continue;
            }

            $entityMethod = new EntityMethod($this->class, $method->name,
                    $attributes);
            $this->beforeChange = array_merge($this->beforeChange,
                    $entityMethod->getBeforeChange());
            $this->afterChange = array_merge($this->afterChange,
                    $entityMethod->getAfterChange());
        }

        $this->refactor();
    }

    /**
     * Returns instance of ReflectionClass.
     * 
     * @return \ReflectionClass
     */
    public function getReflectionClass()
    {
        return new ReflectionClass($this->class);
    }

    /**
     * Returns instance of reflection method for passed method name.
     * 
     * @param   string              $name
     * @return  \ReflectionMethod
     */
    public function getMethod($name)
    {
        return $this->getReflectionClass()
                        ->getMethod($name);
    }

    /**
     * Processes property.
     * 
     * @param   ReflectionProperty $propertyReflection
     * @return  null
     */
    private function processProperty(ReflectionProperty $propertyReflection)
    {
        # Property should not start with underscore.
        if (strpos($propertyReflection->name, '_') === 0) {
            return;
        }

        # Ignoring if no attribute defined on property.
        $attributes = $propertyReflection->getAttributes();
        if (empty($attributes)) {
            return;
        }

        $dataProperty = new
                DataProperty($this->class, $propertyReflection->name,
                $attributes);

        # Ignoring property if @Derived or @DataType not defined.
        if ($dataProperty->isDerived() === false &&
                $dataProperty->getDatatype() === false) {
            return;
        }


        # Let's reslove class name if from is class name or chain of class name and property.
        if ($dataProperty->isDerived()) {
            $derived = $dataProperty->getDerived();
            if ($derived->isFrom() && $this->getProperty($derived->getFrom()) === false) {
                $from = $derived->getFrom();
                $fromArray = explode('.', $from);

                foreach ($fromArray as $index => $from) {
                    if ($this->isEntity($from) === false) {
                        $from = $this->getReflectionClass()->getNamespaceName() . '\\' . $from;
                        if ($this->isEntity($from)) {
                            $fromArray[$index] = $from;
                        }
                    }
                }
                $derived->setFrom(implode('.', $fromArray));
            }
        }

        $this->addProperty($dataProperty, $propertyReflection->name);

        # Identity property of this class.
        if ($dataProperty->getIdentity() === true) {
            $this->setIdentityProperty($propertyReflection->name);
        }
    }

    /**
     * Adds Property to registry.
     * 
     * @param   DataProperty      $property
     * @param   string                                          $name
     */
    private function addProperty(DataProperty $property, $name)
    {
        $this->propertyRule[$name] = $property;
        if ($property->isStatic()) {
            return $this->staticProperties[$name] = false;
        }

        $this->properties[$name] = $name;
        $this->propertyTypes[$property->getPropertyType()][] = $name;
    }

    /**
     * Sets identity property name.
     * 
     * @param   string                          $name
     * @throws  InvalidAttributeException
     */
    private function setIdentityProperty($name)
    {
        if ($this->identity !== false) {
            throw new InvalidAttributeException('Property [' . $this->class .
                            '::' . $name . '] can not be identity as another'
                            . ' property [' . $this->identity . '] is already identity.'
                            . ' There must be only one entity per class.',
                            $this->class, null, 911037);
        }

        $this->identity = $name;
    }

    /**
     * Resolves class dependency.
     * 
     * @return $this
     */
    public function resolveDependency()
    {
        if ($this->dependencyResolved) {
            return $this;
        }

        $this->resolveFromTypeDependecy()
                ->resolveJoinTypeDependency();
        $this->dependencyResolved = true;
        return $this;
    }

    /**
     * Resolves Join type property.
     */
    private function resolveJoinTypeDependency()
    {

        # Derived property can be set by two ways. By defining join parameter 
        # or from parameter. Here processing derived properties which has used
        # join parameter to set property value.
        foreach ($this->getJoinProperties() as $propertyName) {
            $derived = $this->getProperty($propertyName)->getDerived();

            $custom = new CustomJoin($this, $propertyName);

            # Join Table rule.
            $resolved = new ResolvedJoin();
            $resolved->setJoin($custom->getResolvedJoin())
                    ->setClassJoin($derived->getJoin())
                    ->setJoinConnection($custom->getJoinClass())
                    ->setHoldType($derived->getHold())
                    ->setJoinType($derived->getType())
                    ->setGroupBy($derived->getGroup());

            $properties = $derived->getProperty();

            # When property name is not exist, considering property to be object.
            if (empty($properties)) {
                $resolved->addPropertyClass(0, $custom->getLastClass());
                $properties = $custom->getLastAlias();
                goto SKIP_POINT_FOR_LAZY_PROPERTY;
            }

            # Below process is to validate property names and registering
            # their actual enity class along with their data type to @derived
            # attribute.
            foreach ($properties as $index => $name) {
                $exploded = explode('.', $name);
                if (count($exploded) !== 2 ||
                        !$custom->isAliasExist($exploded[0])) {
                    throw new InvalidAttributeException('Invalid'
                                    . ' property name in [Derived]'
                                    . ' attribute for property [' . $this->class . '::' . $propertyName . '].',
                                    $this->class, null, 911038);
                }

                list($alias, $name) = $exploded;

                # First one is class alias and second is property name.
                $className = $custom->getClassOfAlias($alias);
                $resolved->addPropertyClass($name, $className);
                $this->propertyTypes['derivedProperty'][$propertyName][] = $name;
                $property = $this->entity($className)->getProperty($name);
                if ($property === false) {
                    throw new InvalidAttributeException('Property to fetch [' .
                                    $name . '] is does not exist in parent class for'
                                    . ' property [' . $this->class . '::' .
                                    $propertyName . '].', $this->class, null,
                                    911039);
                }
                $derived->registerDataType($name, $property->getDatatype());
                if ($derived->getHold() === ResolvedJoin::HOLD_TYPE_ARRAY) {
                    unset($properties[$index]);
                    $properties[implode('.', $exploded)] = $alias;
                }
            }

            SKIP_POINT_FOR_LAZY_PROPERTY:
            $resolved->setPropertyNameToFetch($properties);
            $this->addJoinTable($propertyName, $resolved);
        }
    }

    /**
     * 
     * @return self
     */
    private function resolveFromTypeDependecy()
    {
        foreach ($this->getJoinFromProperties() as $propertyName) {

            # Derived annotaion of this property.
            $derived = $this->getProperty($propertyName)->getDerived();
            $fromType = new FromProperty(
                    $this, $propertyName, $derived
            );

            $properties = [];

            $resolved = new ResolvedJoin();
            if (empty($derived->getProperty())) {
                $resolved->addPropertyClass(0, $fromType->getLastEntity());
                $properties = $fromType->getLastAlias();
            } else {
                $lastEntity = $this->entity($fromType->getLastEntity());
                $properties = $this->getPropertyNames($propertyName, $derived,
                        $lastEntity);

                foreach ($properties as $index => $name) {
                    $resolved->addPropertyClass($name,
                            $fromType->getLastEntity());

                    if ($derived->getHold() === ResolvedJoin::HOLD_TYPE_ARRAY) {
                        unset($properties[$index]);
                        $properties[$fromType->getLastAlias() . '.'
                                . $name] = $fromType->getLastAlias();
                    }
                }
            }

            $resolved->setParentAlias($fromType->getLastAlias())
                    ->setJoin($fromType->getResolvedJoin())
                    ->setJoinType($fromType->getJoinType())
                    ->setClassJoin($fromType->getClassJoin())
                    ->setJoinConnection($fromType->getJoinConnection())
                    ->setPropertyNameToFetch($properties)
                    ->setHoldType($derived->getHold())
                    ->setGroupBy($fromType->getGroup());

            $fromType->isExtendedJoin() ?
                            $this->addExtendedJoin($propertyName, $resolved) :
                            $this->addJoinTable($propertyName, $resolved);
        }
        return $this;
    }

    /**
     * 
     * @param   string                                          $propertyName
     * @param   Derived      $derived
     * @param   EntityClass           $parent
     * @return  array
     * @throws  InvalidAttributeException
     */
    private function getPropertyNames($propertyName, Derived $derived,
            EntityClass $parent)
    {
        $properties = $derived->getProperty();
        $propertyNames = [];
        foreach ($properties as $name) {

            # Checking if property exist in parent class or not.
            if ($parent->getProperty($name) === false) {
                throw new InvalidAttributeException('Property to fetch [' .
                                $name . '] for [' . $derived->getPropertyName() . '] is does'
                                . ' not exist in parent class [' . $parent->getClass() .
                                '].', $derived->getClass(), null, 911040);
            }

            # We do not allow proeprty to derived from proeprty which derived
            # property of another class.
            if ($parent->getProperty($name)->isDerived()) {
                throw new NotSupportedException('Property [' . $this->class .
                                '::' . $propertyName . '] can not derive property which is '
                                . 'derived property of another class.',
                                $this->class, null, 911041);
            }

            $derived->registerDataType($name,
                    $parent->getProperty($name)->getDataType());
            $propertyNames[] = $name;
            $this->propertyTypes['derivedProperty'][$propertyName][] = $name;
        }
        return $propertyNames;
    }

    /**
     * Prepares property list to be selected form query.
     * 
     * @return NULL
     */
    private function preparePropertiesToSelect($unfetchable = [], $query = null,
            $join = false)
    {
        $properties = [];
        foreach ($this->getFetchableProperties() as $name) {
            $column = $this->entity->getName() . '.' . $name;

            # Proceed only if
            # 1. Property does not exist in unfetchable
            # 2. Property need to decrypted on DB side.
            # 3. Property need to be decrypted.
            if (array_key_exists($name, $unfetchable) === false &&
                    $this->isDBEncryption() &&
                            $this->getProperty($name)
                            ->getDatatype()
                            ->getEncrypt()) {
                $name .= Query::AS_IT_IS;
                $column = $this->getEncrypter($query)->decrypt($column);
            }
            $properties[$name] = $column;
        }
        $properties[DataProperty::EXTRA_PROPERTY] = $this->entity->getName() .
                '.' . DataProperty::EXTRA_PROPERTY;
        $this->nojoinSelect = $properties;

        # Will not prepare join select in case of select column needed
        # for no join only.
        if ($join === false) {
            return;
        }

        # We will now add property which requires join.
        foreach ($this->getJoinTable() as $propertyName => $joinConfig) {
            # Will not add property to list which are added to unfetchable list.
            if (array_key_exists($propertyName, $unfetchable)) {
                continue;
            }

            $property = $this->getProperty($propertyName);
            $derived = $property->getDerived();
            if (empty($derived->getProperty())) {
                continue;
            }

            # Checking if property from which it is deriving exist in unfetchable.
            $from = $derived->getFrom();
            if ($from !== false && array_key_exists($from, $unfetchable)) {
                continue;
            }

            # If hold type is array we do not need this in main query.
            if ($joinConfig->getHoldType() === ResolvedJoin::HOLD_TYPE_ARRAY) {
                continue;
            }

            foreach ($joinConfig->getPropertyNameToFetch() as $key => $name) {
                if (strpos($name, '.') === false) {
                    $actualName = $name;
                    $key = $propertyName . '_' . $name;
                    $name = $joinConfig->getParentAlias() . '.' . $name;
                } else {
                    $actualName = StringUtility::getExplodeLast('.', $name);
                }

                # Proceed only if encryption is to be done on DB side
                if ($this->isDBEncryption() && $query !== null) {

                    # Will now fetch entity of actual property name and will
                    # check if propertuy need to decrypted.
                    $dataType = $this->entity($joinConfig
                                    ->getPropertyClass($actualName))
                            ->getProperty($actualName)
                            ->getDatatype();
                    if ($dataType->getEncrypt()) {

                        # In this we should consider expression as it is.
                        $key .= Query::AS_IT_IS;
                        $name = $this->getEncrypter($query)->decrypt($name);
                    }
                }


                $properties[$key] = $name;
            }
        }
        $this->joinSelect = $properties;
    }

    /**
     * Returns entity's property value from instance.
     * 
     * @param   object      $instance
     * @param   array       $fetched
     * @return  array
     */
    public function getPropertyValues($instance, $fetched = false)
    {
        $returning = [];
        foreach (array_keys($this->properties) as $name) {
            $property = $this->getProperty($name);
            if ($fetched && $property->isDerived() === true) {
                continue;
            }

            $value = $property->getValueFromEntity($instance);

            if (is_object($value)) {
                $value = clone $value;
            }
            $returning[$name] = $value;
        }
        return $returning;
    }

    /**
     * Iterates over each entity record and then validates it.
     * 
     * @param type $instance
     */
    public function validateEntityRecord($instance, $fetchedInstance,
            $skipRelativeValidation = false)
    {
        $passed = [];
        foreach ($this->getProperties() as $name) {

            # Derived property is not updateable so we will not do any 
            # validation on it.
            $property = $this->getProperty($name);
            if ($property->getDerived() !== null) {
                continue;
            }

            $value = $property->getValueFromEntity($instance);

            if ($value === null) {
                $value = $property->getDatatype()->getDefault();
            }

            $property->validate($fetchedInstance, $value);

            if ($skipRelativeValidation === false) {
                $property->getRelative() && $this->validateRelative($property,
                                $value);
            }

            $value !== null && $passed[$name] = $value;
        }
        return $passed;
    }

    /**
     * Validates relative property.
     * If property has some value it should be belongs to relative property.
     * It can be null only if relative type is loose.
     * 
     * @param DataProperty $property
     * @param mixed $value
     * @return boolean
     * @throws ApplicationException
     */
    public function validateRelative(DataProperty $property, $value)
    {
        # Relative attribute.
        $relative = $property->getRelative();
        if (empty($value)) {
            # Allowing null if type is loose.
            if ($relative->getType() === Relative::LOOSE) {
                return true;
            }
            throw new ApplicationException('Property [' . $this->class . '::' .
                            $property->getPropertyName() . '] can not be null or empty as its relative to [' .
                            $relative->getTo() . '].', $this->class, null,
                            911042);
        }

        # Now we will first get the relative class and then execute query
        # to find if the $value belongs to relative class or not.
        $entity = $this->entity($relative->getTo());

        # If relative annotaiton not have defined relative property name we will
        # use identity of relative class.
        $relativeProperty = ($relative->getName() === null ?
                $entity->getIdentity() : $relative->getName());
        $exist = $entity->getQuery()
                ->setCondition($relativeProperty, $value)
                ->count();
        if ($exist === 0) {
            throw new ApplicationException('Value of relative property [' .
                            $this->class . '::' . $property->getPropertyName() . '] must'
                            . ' belongs to relative property [' . $relative->getTo() . '::' .
                            $relativeProperty . '].', $this->class, null, 911043);
        }
    }

    /**
     * Returns property which is relative to passed class's identity.
     * 
     * @param   string      $class
     * @return  boolean
     */
    public function getRelativeOf($class)
    {
        # We need indentity property name to check if property is relative to
        # identity property only.
        $identity = $this->entity($class)->getIdentity();

        foreach ($this->getProperties() as $name) {
            $relative = $this->getProperty($name)->getRelative();

            # We will conisder property which relative to another class's 
            # property.
            if ($relative !== null && $relative->getTo() === $class) {
                # Consdering property which is relative to class's identity property only.
                if ($relative->getName() !== null && $relative->getName() !== $identity) {
                    continue;
                }
                return $this->getProperty($name);
            }
        }
        return false;
    }

    /**
     * Returns property name to which given property is relative to.
     * 
     * @param   string                                          $from
     * @param   string                                          $propertyName
     * @return  InvalidAttributeExceptionProperty
     * @throws  InvalidAttributeException
     */
    public function getRelativePropertyName($propertyName)
    {
        $from = $this->getProperty($propertyName)->getDerived()->getFrom();
        if ($from === null) {
            return $this->getIdentity();
        }

        list($fromPropertyName) = explode('.', $from);

        $relativePropertyName = false;
        $parent = $this;
        if (($property = $parent->getProperty($fromPropertyName)) instanceof DataProperty) {
            $relativePropertyName = $property->getPropertyName();
        } else if (($property = $this->entity($fromPropertyName)
                ->getRelativeOf($parent->getClass())) !== false) {
            $relativePropertyName = $property->getPropertyName();
        }

        if ($relativePropertyName !== false) {
            return $relativePropertyName;
        }

        throw new InvalidAttributeException('Not able to find relative'
                        . ' property either in same class or relative class for'
                        . ' [' . $this->class . '::' . $propertyName . '].',
                        $this->class, null, 911044);
    }

    /**
     * Sets after change trigger.
     * 
     * @param array $afterChange
     */
    private function setAfterChange(AfterChange $afterChange)
    {
        $this->afterChange[] = $afterChange;
    }

    /**
     * Sets before change trigger.
     *  
     * @param array $beforeChange
     */
    private function setBeforeChange(BeforeChange $beforeChange)
    {
        $this->beforeChange[] = $beforeChange;
    }

    /**
     * Executes before change events.
     * 
     * @param   object      $old
     * @param   object      $new
     * @param   string      $mode
     * @param   array       $updatedNames
     */
    public function executeBeforeChange($old, $new, $mode, $updatedNames)
    {
        $eventCalled = 0;
        $name = 'isFor' . ucfirst(strtolower($mode));
        foreach ($this->beforeChange as $trigger) {
            if ($this->isCallable($trigger, $name)) {
                $eventCalled++;
                if ($this->executeCallback($trigger->getCallback(),
                                [$old, $new, $mode, $updatedNames]) === false) {
                    return false;
                }
            }
        }
        return $eventCalled;
    }

    /**
     * Returns true if current before change event is callbable.
     * 
     * @param type $event
     * @param type $name
     * @return type
     */
    private function isCallable($event, $name)
    {
        return $this->invokeMethod([$event, $name]) &&
                $this->isCallbackExist([
                    $event->getCallbackClass(),
                    $event->getCallbackMethod()
        ]);
    }

    /**
     * Executes callback method.
     * 
     * @param type $callback
     * @param type $parameter
     * @return type
     */
    private function executeCallback($callback, $parameter)
    {
        return $this->invokeMethod([new $callback[0], $callback[1]], $parameter);
    }

    /**
     * Executes after change events.
     * 
     * @param   object      $old
     * @param   object      $new
     * @param   string      $mode
     * @param   array       $updatedNames
     */
    public function executeAfterChangeEvent($old, $new, $mode, $updatedNames)
    {
        $name = 'isFor' . ucfirst(strtolower($mode));
        foreach ($this->afterChange as $trigger) {
            if ($this->isCallable($trigger, $name)) {
                $this->executeCallback($trigger->getCallback(),
                        [$old, $new, $mode, $updatedNames]);
            }
        }
    }

    public function getSelf()
    {
        $this->reflection = NULL;
        return $this;
    }

    /**
     * Returns property rules.
     * 
     * @return array
     */
    public function getPropertyRules()
    {
        return $this->propertyRule;
    }

}
