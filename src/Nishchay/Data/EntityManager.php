<?php

namespace Nishchay\Data;

use Nishchay;
use ReflectionClass;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Data\AbstractEntityStore;
use Nishchay\Data\Annotation\Entity;
use Nishchay\Data\Annotation\Property\Property;
use Nishchay\Data\Annotation\Property\Relative;
use Nishchay\Data\EntityQuery;
use Nishchay\Data\Meta\MetaTable;
use Nishchay\Data\Property\ConstantEntity;
use Nishchay\Data\Property\ConstantProperty;
use Nishchay\Data\Property\ResolvedJoin;
use Nishchay\Data\Query;
use Nishchay\Security\Encrypt\EncryptTrait;
use Nishchay\Utility\Coding;
use Nishchay\Utility\StringUtility;
use stdClass;

/**
 * Entity Manager class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class EntityManager extends AbstractEntityStore
{

    use EncryptTrait;

    /**
     * Constant for insert mode of entity.
     */
    const INSERT = 'insert';

    /**
     * Constant for update mode of entity.
     */
    const UPDATE = 'update';

    /**
     * Constant for remove mode of entity.
     */
    const REMOVE = 'remove';

    /**
     * Connection name of entity.
     * 
     * @var string 
     */
    private $entityConnection;

    /**
     * Actual entity name.
     * Database table name to which enitty class will be mapped to.
     * 
     * @var string 
     */
    private $entityTable;

    /**
     * Fetched columns and their values from database.
     * Helps us saving columns while primary key is missing.
     * 
     * @var array 
     */
    private $fetched = [];

    /**
     * Instance of entity class with it's value same as fetched.
     * 
     * @var object 
     */
    private $fetchedInstance;

    /**
     * Flag for record can be updated or not.
     * 
     * @var boolean 
     */
    private $isEntityUpdateable = true;

    /**
     * Flag for record is in insert or update mode.
     * 
     * @var boolean 
     */
    private $isEntityInsertable = null;

    /**
     * Entity class name.
     * That's actual class name.
     * 
     * @var string 
     */
    private $entityClass;

    /**
     * Whether to set derived object while making object.
     * 
     * @var boolean 
     */
    private $isLazyEnabled = true;

    /**
     * Whether to set derived property while making object.
     * 
     * @var boolean 
     */
    private $isDerivedEnabled = true;

    /**
     * Entity extra property.
     * 
     * @var array 
     */
    private $extraProperty = [];

    /**
     * Original values extra properties with its name.
     * 
     * @var array 
     */
    private $extraOriginal = [];

    /**
     * This property becomes true if is was returned by this class.
     * 
     * @var boolean 
     */
    private $isEnityReturned = false;

    /**
     * Properties which should not be fetched from  database
     * 
     * @var array 
     */
    private $unFetchAbleProperties = [];

    /**
     * Instance of ReflectionClass on entity class.
     * 
     * @var \ReflectionClass 
     */
    private $reflectionEntity;

    /**
     * Instance of entity class.
     * 
     * @var object 
     */
    private $entityInstance;

    /**
     * Temporary data.
     * 
     * @var array 
     */
    private $tempData = [];

    /**
     * Flag for skipping relative validation.
     * 
     * @var bool 
     */
    private $skipRelativeValidation = false;

    /**
     * 
     * @param string|object $class
     */
    public function __construct($class)
    {
        $this->init($class);
    }

    /**
     * Initializes entity.
     * 
     * @param string $class
     */
    private function init($class)
    {
        # $class parameter accepts both string and instnace of enitty class.
        # But we need only class name of entity class.
        $this->entityClass = is_string($class) ?
                $class : get_class($class);

        # Getting connection name from @connect annotaiton defined on entity
        # class. It will return default connection name if entity class has 
        # not defined @connect annotation on it. We are setting to this 
        # class because we use it at many places.
        $this->entityConnection = $this->getThisEntity()
                ->getConnect();

        # This is the name of databaase table to which this enitty class will be
        # mapped to. We will persist everything to this entity name of this 
        # entity class.
        $this->entityTable = $this->getThisEntity()
                ->getEntity()
                ->getName();

        # Derived property and lazy property can also enabled or disabled 
        # globaly from databse setting file. We will here apply same value from
        # database setting to this enitty manager.
        $this->isDerivedEnabled = $this->getSettingFlag('derivedProperty');
        $this->isLazyEnabled = $this->getSettingFlag('lazyProperty');
    }

    /**
     * Returns flag value from database settings. This will returns false 
     * if database setting value type other than boolean.
     * 
     * @return  boolean
     */
    private function getSettingFlag($flagName)
    {
        $flag = Nishchay::getSetting('database.global.' . $flagName);
        return is_bool($flag) ? $flag : false;
    }

    /**
     * Sets value of property after validating.
     * 
     * @param   string                  $name
     * @param   mixed                   $value
     * @return  boolean
     * @throws  ApplicationException
     */
    public function __set($name, $value)
    {
        if (!$this->isPropertyExist($name) && !$this->isExtraPropertyExists($name)) {
            throw new ApplicationException('Property [' . $this->entityClass .
                    '::' . $name . '] does not exists.', 1, null, 911064);
        }

        if ($this->isExtraPropertyExists($name)) {
            return $this->setExtraProperty($name, $value);
        }

        $property = $this->getThisEntity()
                ->getProperty($name);

        # We should validate new value before assiging.
        $property->validate($this->getInstance(), $value);
        $property->updateValueToEntity($value, $this->getInstance());
        return true;
    }

    /**
     * Class method defined in current entity class.
     * 
     * @param   string      $name
     * @param   array       $arguments
     * @return  mixed
     */
    public function __call($name, $arguments)
    {
        # We first must check that calling method exist in entity class or not.
        if (!method_exists($this->entityClass, $name)) {
            throw new ApplicationException('Method [' . $this->entityClass .
                    '::' . $name . '] does not exists.');
        }

        # We will not allow non public method to be called from outside class.
        $method = $this->getThisEntity()->getMethod($name);
        if ($method->isPublic() === false) {
            throw new NotSupportedException('Can not call non public method [' .
                    $this->entityClass . '::' . $name . '] from outside class.', 1, null, 911065);
        }

        $instance = null;
        # Need instance only if method is not static.
        if ($method->isStatic() === false) {
            $instance = $this->getInstance();
        }

        $method->setAccessible(true);
        return $method->invokeArgs($instance, $arguments);
    }

    /**
     * Returns instance of current entity class.
     * 
     * @return object
     * @throws \Nishchay\Exception\ApplicationException
     */
    public function getInstance()
    {
        if ($this->entityInstance === null) {

            return $this->entityInstance = $this->getThisEntity()
                    ->getReflectionClass()
                    ->newInstance();
        }

        return $this->entityInstance;
    }

    /**
     * Returns instance of reflection class for current entity class.
     * 
     * @return \ReflectionClass
     */
    private function getReflectionClass()
    {
        if ($this->reflectionEntity === null) {
            return $this->reflectionEntity = new ReflectionClass($this->entityClass);
        }

        return new ReflectionClass($this->entityClass);
    }

    /**
     * 
     * @param type $name
     * @param type $value
     * @return boolean
     */
    private function setExtraProperty($name, $value)
    {
        $extra = $this->extraProperty[$name];
        $rule = $extra['rule'];
        $rule->validate($value, $extra['value'] !== null);
        $this->extraProperty[$name]['value'] = $value;
        return true;
    }

    /**
     * Returns value of property.
     * 
     * @param   string      $name
     * @return  mixed
     * @throws  ApplicationException
     */
    public function __get($name)
    {
        # Of course first priority is of properties defiend by entity class.
        # If property exist we will fetched value from enitty class instnace
        # only if accessing property is public.
        if ($this->isPropertyExist($name)) {
            return $this->getPropertyValue($name);
        }
        # We will now return property value from extra property from extra 
        # properties set to this class. Extra properties are public so we don't
        # it can be accessed from outside class.
        else if ($this->isExtraPropertyExists($name)) {
            return $this->extraProperty[$name]['value'];
        }

        throw new ApplicationException('Property [' . $this->entityClass .
                '::' . $name . '] does not exists.', 1, null, 911066);
    }

    /**
     * Returns TRUE if property exists. Also returns true if property is extra property.
     * 
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return $this->isPropertyExist($name) || $this->isExtraPropertyExists($name);
    }

    /**
     * Returns property value from $_updated if it is updated otherwise from
     * $_assigned.
     * 
     * @param type $name
     * @return type
     */
    private function getPropertyValue($name)
    {
        return $this->getThisEntity()
                        ->getProperty($name)
                        ->getValueFromEntity($this->getInstance());
    }

    /**
     * Returns true if class has defined this property.
     * 
     * @param   string      $name
     * @return  boolean
     */
    public function isPropertyExist($name)
    {
        return $this->getThisEntity()->getProperty($name) === false ?
                false : true;
    }

    /**
     * Fetches single record by given identity value.
     * 
     * @param   string|int                      $id
     * @return \Nishchay\Data\EntityManager
     */
    public function get($id)
    {
        $object = $this->getSelf();
        $found = $object->setPropertyValues($this->getSelectiveQuery($this->isDerivedEnabled)
                        ->setCondition($this->getThisEntity()->getEntity()->getName() . '.' . $this->getThisEntity()
                                ->getIdentity(), $id)
                        ->getOne()
        );

        if ($found === false) {
            return false;
        }
        $object->isEnityReturned = true;
        $this->isLazyEnabled && $this->processLazyProperties([$object]);
        return $object;
    }

    private function getAgregate(string $propertyName, string $agregateName)
    {

        $property = $this->getThisEntity()->getProperty($propertyName);
        if ($property === false) {
            throw new ApplicationException('[' . $this->entityClass . '::' .
                    $propertyName . '] does not exists.', 1, null);
        }

        $value = (new Query())
                ->setTable($this->entityTable)
                ->{$agregateName}($propertyName);

        $object = $this->getSelf();
        $object->setPropertyValues([$propertyName => $value]);

        return $object->{$propertyName};
    }

    /**
     * 
     * @param string $propertyName
     * @return type
     */
    public function max(string $propertyName)
    {
        return $this->getAgregate($propertyName, 'max');
    }

    /**
     * 
     * @param string $propertyName
     * @return type
     */
    public function min(string $propertyName)
    {
        return $this->getAgregate($propertyName, 'min');
    }

    /**
     * Disable or enable lazy properties to be set while 
     * fetching record.
     * 
     * @param boolean $flag
     */
    public function enableLazy($flag)
    {
        $this->isLazyEnabled = (bool) $flag;

        return $this;
    }

    /**
     * Disable or enable lazy object to set while
     * fetching record.
     * 
     * @param boolean $flag
     * @return $this
     */
    public function enableDerived($flag)
    {
        $this->isDerivedEnabled = (bool) $flag;

        return $this;
    }

    /**
     * Returns instance of this class for returning entity as record.
     * 
     * @return \Nishchay\Data\EntityManager
     */
    private function getSelf()
    {
        $self = new static($this->entityClass);
        $self->isLazyEnabled = $this->isLazyEnabled;
        $self->isDerivedEnabled = $this->isDerivedEnabled;
        $self->unFetchAbleProperties = $this->unFetchAbleProperties;
        return $self;
    }

    /**
     * Converts row data to entity.
     * Returning array contains key as value of property from which data were
     * retrieved. This helps us finding derived value easily.
     * 
     * @param   \Nishchay\Data\EntityQuery $entityQuery
     * @param   string $propertyName
     * @return  array
     */
    private function refactorDerivedResult(EntityQuery $entityQuery, $propertyName)
    {
        $records = $entityQuery->getQueryBuilder()->get();
        $derivedData = [];

        # This will return mapping of alias to its entity class.
        $mapping = $entityQuery->getReturnableEntity();


        unset($mapping[$this->entityTable]);
        foreach ($records as $row) {
            # Storing value as index so that we can easily retrieve it
            # at the calling side.
            $derivedData[$row->{$propertyName}][] = $this->getRowAsEntity($row, $mapping, $entityQuery);
        }
        return $derivedData;
    }

    /**
     * Returns all unique values of given column name from result.
     * 
     * @param   array   $result
     * @param   string  $propertyName
     * @return  array
     */
    private function getUniqueRecords($result, $propertyName)
    {
        $returning = [];
        foreach ($result as $index => $row) {
            # We use this method from various places. Sometimes they need 
            # updated value. Updated value has higher priority then assigned. 
            # While making record or fetching all records,Object might not 
            # have updated value so it always returns assigned value.
            $propertyValue = $this->getThisEntity()
                    ->getProperty($propertyName)
                    ->getValueFromEntity($row->getInstance());
            $value = current($this->getStoreable($propertyValue));

            # We only need unique values.
            if ($value !== null && !in_array($value, $returning)) {
                $returning[$index] = $value;
            }
        }
        return $returning;
    }

    /**
     * Set or reset object property of record.
     * 
     */
    public function setLazy($records = [])
    {
        # Setting derived property requries derived object 
        # flag to be true.So we first need to active it 
        # then we will revert it back to it's acutal flag.
        $actual = $this->isLazyEnabled;
        !$this->isLazyEnabled && $this->enableLazy(true);

        if ($records instanceof DataIterator) {
            $records = $records->getArrayCopy();
        } else if (empty($records)) {
            $records = [$this];
        }

        $this->processLazyProperties($records);

        # Revert to actual.
        $this->enableLazy($actual);

        return $records;
    }

    /**
     * Iterate over record, processes, validate or 
     * convert it and then set it to assigned value.
     * 
     * @param   object                  $row
     * @throws  ApplicationException
     */
    private function setPropertyValues($row)
    {
        $this->flush();

        if ($row === false) {
            return false;
        }

        # Updating fetchable columns.
        foreach ($row as $column => $value) {
            $property = $this->getThisEntity()
                    ->getProperty($column);

            # We are storing extra in _extra_property to make it seperated 
            # from actual property. If the unserializaion fails we take it 
            # empty array. Same value saved only if record saved by user.
            if ($property === false && $column === Property::EXTRA_PROPERTY) {
                $this->setExtraProperties($value);
                continue;
            }

            # Will not process if property does not exist or 
            # property is derived.
            if ($property === false || $property->getDerived() !== false) {
                continue;
            }

            # Storing fetched value as it helps updating same record in the 
            # case of primary key is missing.
            $value = $property->getValue($value);
            $this->fetched[$column] = is_object($value) ?
                    (clone $value) : $value;
            $property->updateValueToEntity($property->applySetter($value), $this->getInstance());
        }

        $this->setDerivedProperties($row);

        # Updating callback properties.
        $this->setCallbackProperties();
        return true;
    }

    /**
     * Sets property's value which require callback from its callback response.
     * 
     */
    private function setCallbackProperties()
    {
        $properties = $this->getThisEntity()
                ->getCallbackProperties();

        foreach ($properties as $name) {
            $this->getThisEntity()
                    ->getProperty($name)
                    ->updateValueToEntity($this->getCallbackValue($name), $this->getInstance());
        }
    }

    /**
     * Sets extra properties to class.
     * 
     * @param type $value
     */
    private function setExtraProperties($value)
    {
        if (empty($value)) {
            return;
        }
        if (Coding::isUnSerializable($value) === false) {
            $value = hex2bin($value);
        }
        $this->extraProperty = Coding::unserialize($value);
        foreach ($this->extraProperty as $propertyName => $rule) {
            $this->extraOriginal[$propertyName] = $rule['value'];
        }
    }

    /**
     * Sets Derived property of this class.
     * 
     * @param object $row
     */
    private function setDerivedProperties($row)
    {
        # Will not set derived property's value if derived property is flagged 
        # not to set OR entity class does not has any derived property.
        if ($this->isDerivedEnabled === false ||
                        $this->getThisEntity()
                        ->isDerivedPropertyExist() === false) {
            return false;
        }

        foreach ($this->getThisEntity()
                ->getDerivedProperties() as $self => $toAssign) {

            # Discarding property found in unfetchable
            if (array_key_exists($self, $this->unFetchAbleProperties)) {
                continue;
            }

            $property = $this->getThisEntity()
                    ->getProperty($self);
            $joinTable = $this->getThisEntity()
                    ->getJoinTable($self);

            # Callback properties are also known as derived properties, in that
            # case join table returns false. Will not proceed if join table
            # returns false.
            if ($joinTable === false) {
                continue;
            }

            $this->setDerivedPropertyValues($self, $toAssign, $row, $property, $joinTable);
        }
    }

    /**
     * Sets multiple properties to given property.
     * 
     * @param   string                                            $propertyName
     * @param   array                                             $derivedProperties
     * @param   \stdClass                                         $row
     * @param   \Nishchay\Data\Annotation\Property\Property        $property
     * @param   \Nishchay\Data\Property\ResolvedJoin               $joinTable
     */
    private function setDerivedPropertyValues($selfPropertyName, $derivedProperties, $row, $property, $joinTable)
    {
        # Property can have more than one from another class, we will here
        # iterate over each to assign.
        $derivedValue = [];
        foreach ($derivedProperties as $propertyName) {
            # Not fetched from databse! No need to procecss furhter.
            $propertyValue = false;

            if ($property->getDerived()->isFrom()) {
                # Derived properties are fetched as
                # {selfPropertyName}_{propertyToFetch}
                $selectPropertyName = $selfPropertyName . '_' . $propertyName;
                if (property_exists($row, $selectPropertyName)) {
                    $propertyValue = $row->{$selectPropertyName};
                }
            } else if (property_exists($row, $propertyName)) {
                $propertyValue = $row->{$propertyName};
            }

            if ($propertyValue !== false) {
                # Applying setter of relative.
                $relativeClass = $joinTable->getPropertyClass($propertyName);
                $derivedValue[$propertyName] = $this->entity($relativeClass)
                        ->getProperty($propertyName)
                        ->applySetter($property->getValue($propertyValue, $propertyName));
            }
        }
        # If derived property is deriving only one property then that property
        # value assiging directly to derived property.
        if (count($derivedProperties) === 1) {
            $derivedValue = array_key_exists($propertyName, $derivedValue) ?
                    $derivedValue[$propertyName] : null;
        } else {
            $derivedValue = !empty($derivedValue) ?
                    (new ConstantProperty($derivedValue, $this->entityClass .
                            '::' . $propertyName)) : null;
        }

        # Calling self class setter method if exists.
        $property->updateValueToEntity($property->applySetter($derivedValue), $this->getInstance());
    }

    /**
     * Returns all fetched rows in DataIterator class.
     * 
     * @return \Nishchay\Data\DataIterator
     */
    public function getAll()
    {
        $records = $this->getSelectiveQuery($this->isDerivedEnabled)
                ->get();

        if (empty($records)) {
            return $records;
        }

        $iterator = [];
        foreach ($records as $row) {
            $object = $this->getSelf();
            $object->setPropertyValues($row);
            $object->isEnityReturned = true;
            $iterator[] = $object;
        }
        $dataIterator = new DataIterator($this->processLazyProperties($iterator));

        $this->flush();
        return $dataIterator;
    }

    /**
     * Lazy properties means property whose value requires separate query.
     * 
     * @param   array   $records
     * @return  array
     */
    private function processLazyProperties($records)
    {
        if ($this->isLazyEnabled === false) {
            return $records;
        }

        foreach ($this->getThisEntity()->getJoinTable() as $propertyName => $joinTable) {

            if (array_key_exists($propertyName, $this->unFetchAbleProperties)) {
                continue;
            }

            # Properties to be fetcehd and assigned to $propertyName. If this
            # is blank we will fetch all propertes from mentioned class and
            # assign it to $propertyName.
            $propertyNames = $this->getThisEntity()
                    ->getProperty($propertyName)
                    ->getDerived()
                    ->getProperty();

            # Some joins are not lazy. Lazy means join requires seperate query
            # rather than combining with main query to fetch record. Derived
            # properties whih holds array or whole entity is called laxy property.  
            if ($joinTable->getHoldType() === ResolvedJoin::HOLD_TYPE_ARRAY ||
                    $propertyNames === false) {
                $relativeProperty = $this->getThisEntity()
                        ->getRelativePropertyName($propertyName);
                $this->setLazyProperty($records, $joinTable, $relativeProperty, $propertyName);
            }
        }

        return $records;
    }

    /**
     * Fetches, refactors and sets it to lazy property.
     * 
     * @param array $records
     * @param ResolvedJoin $join
     * @param string $propertyName
     * @param string $selfProperty
     */
    private function setLazyProperty(&$records, ResolvedJoin $join, $propertyName, $selfProperty)
    {
        # Finding unique value from records.
        $values = $this->getUniqueRecords($records, $propertyName);

        if (empty($values)) {
            return;
        }

        # Preparing query to fetch records for derived property. We will fetch
        # record for each row in $records all at once.
        $entityQuery = new EntityQuery($this->entityConnection);
        $entityQuery->setTable($this->entityClass, $this->entityTable)
                ->addJoin($join->getClassJoin())
                # We need self property to be fetched again as it helps us 
                # arranging fetcehd records.
                ->setProperty($this->entityTable . '.' . $propertyName)
                ->setCondition([
                    $this->entityTable . '.' . $propertyName . '[+]' => $values
        ]);

        if ($join->getGroupBy() !== false) {
            $entityQuery->setGroupBy($join->getGroupBy());
        }

        $propertyNames = $this->getThisEntity()
                ->getProperty($selfProperty)
                ->getDerived()
                ->getProperty();
        $entityQuery->setProperty($join->getPropertyNameToFetch());
        # This will convert retrieved records into entity.
        $derived = $this->refactorDerivedResult($entityQuery, $propertyName);

        # $property_names false means derived property should be entity instance.
        if ($propertyNames !== false) {
            $derived = $this->extractFromDerived($derived, $this->getAssignableMapping($join), $selfProperty);
        }

        $property = $this->getThisEntity()
                ->getProperty($propertyName);

        # Iterating over each records to set what we have derived.
        foreach ($records as $index => $row) {
            # Getting value from current row(entity) which will be treated as
            # offset. As $derived got index based on which value has been 
            # fetched. We will look for offset as key in $derived.
            $offset = $property->getValueFromEntity($row->getInstance());
            if (!array_key_exists($offset, $derived)) {
                # If the derived property should be perfect!
                # We will remove this row from $records.
                if ($join->getJoinType() === Relative::PERFECT) {
                    unset($records[$index]);
                }
                continue;
            }

            $value = $derived[$offset];

            # We found value to be set to this row, but hey $value is array. 
            # So we will take first element from $value if the hold type is single.
            if ($join->getHoldType() !== ResolvedJoin::HOLD_TYPE_ARRAY) {
                $value = current($value);
            }
            $this->getThisEntity()
                    ->getProperty($selfProperty)
                    ->updateValueToEntity($value, $row->getInstance());
        }
    }

    /**
     * Extracts properties from derived values to be assigned to.
     *  
     * @param   array   $derived
     * @param   array   $assignable
     * @param   string  $selfProperty
     * @return  array
     */
    private function extractFromDerived($derived, $assignable, $selfProperty)
    {
        # Derived data contains insatance of entity class of derived property.
        # Because this is called for any type derived type, derived values 
        # stored as array. 
        foreach ($derived as $index => $values) {
            foreach ($values as $key => $entry) {
                if ($entry === null) {
                    continue;
                }
                $assigned = [];
                foreach ($assignable as $name => $alias) {
                    $assigned[$name] = $entry instanceof \stdClass ?
                            $entry->{$alias}->{$name} :
                            $entry->{$name};
                }
                $values[$key] = new ConstantProperty($assigned, $this->entityClass . '::' . $selfProperty);
            }
            $derived[$index] = $values;
        }
        return $derived;
    }

    /**
     * Returns properties which should be assigned to instance with it's mapping
     * class name.
     * 
     * @param ResolvedJoin $join
     * @return type
     */
    private function getAssignableMapping(ResolvedJoin $join)
    {
        $toAssign = array_keys($join->getPropertyNameToFetch());
        foreach ($toAssign as $key => $value) {
            unset($toAssign[$key]);
            $explode = explode('.', $value);
            $toAssign[$explode[1]] = $explode[0];
        }
        return $toAssign;
    }

    /**
     * Returns column to be selected.
     * 
     * @return array
     */
    private function getSelectiveColumn($entity, $query = null, $derived = false)
    {
        return $derived ?
                $entity->getJoinSelect($this->unFetchAbleProperties, $query) :
                $entity->getNoJoinSelect($this->unFetchAbleProperties, $query);
    }

    /**
     * Returns query with join clause if any exist.
     * 
     * @return \Nishchay\Data\EntityQuery
     */
    private function getSelectiveQuery($fetchDerived, $entityQuery = false)
    {
        if ($this->isEnityReturned) {
            throw new ApplicationException('This is returned entity and'
                    . ' not allowed to re-fetch record.', 1, null, 9110067);
        }

        $entity = $this->getThisEntity();

        $builder = new EntityQuery($this->entity($this->entityClass)->getConnect());
        $builder->setTable($this->entityClass, $this->entityTable);

        if ($fetchDerived) {
            foreach ($entity->getJoinTable() as $propertyName => $join) {
                # Will not add join if property exists in unfetchable or hold
                # type is multiple.
                if (array_key_exists($propertyName, $this->unFetchAbleProperties) ||
                        $join->getHoldType() === ResolvedJoin::HOLD_TYPE_ARRAY) {
                    continue;
                }

                $derived = $this->getThisEntity()->getProperty($propertyName)
                        ->getDerived();

                # Will not add join if derived property's 'from' property exists
                # unfetchable or deriving whole entity.
                if ($derived->getProperty() === false ||
                        ($derived->getFrom() !== false &&
                        array_key_exists($derived->getFrom(), $this->unFetchAbleProperties))) {
                    continue;
                }

                $builder->addJoin($join->getClassJoin());

                if ($derived->getGroup() !== false) {
                    foreach ($derived->getGroup() as $by) {
                        $builder->setGroupBy($this->entityTable . '.' . $by);
                    }
                }
            }
        }

        if ($entityQuery) {
            return $builder;
        }
        $query = $builder->getQueryBuilder();

        # We will set column based on  derived property to be set or not.
        $query->setColumn($this->getSelectiveColumn($entity, $query, $fetchDerived));
        $query->setOrderBy($this->entityTable . '.' . $entity->getIdentity());

        return $query;
    }

    /**
     * Remove property to be set while setting property from
     * database.
     * 
     * @param   string|array  $propertyName
     */
    public function setUnFetchable($propertyName)
    {
        foreach ((array) $propertyName as $name) {
            $this->unFetchAbleProperties[$name] = true;
        }

        return $this;
    }

    /**
     * Resets unfetchable to its default.
     * By default all properties except derived object are fetchable.
     * So default value of unfetchable is empty.
     * 
     */
    public function resetUnFetchable()
    {
        $this->unFetchAbleProperties = [];
    }

    /**
     * Resets property to their original values.
     */
    private function flush()
    {
        $this->extraProperty = [];
        $this->isEntityInsertable = null;
    }

    /**
     * When record is saved, we give all updated properties to _fetched 
     * property. This makes further save only reflectable if any change 
     * made after previous save.
     * If the current entity record was just save, this will make this
     * entity to update mode.
     * 
     */
    private function reflectConfig()
    {
        $this->fetched = $this->getThisEntity()
                ->getPropertyValues($this->getInstance());
        $this->fetchedInstance = null;
        $this->isEntityInsertable = false;
    }

    /**
     * Checks current values are in insert or update mode.
     * 
     * @return boolean
     */
    private function isInsertable()
    {
        if ($this->isEntityInsertable !== null) {
            return $this->isEntityInsertable;
        }

        # Record is insertable while joining all elements of fetched property
        # of this class result in empty string.  fetched gets value when 
        # record is fetched from database otherwise all values are null or
        # record has been updated or inserted.
        return ($this->isEntityInsertable = empty(implode('', $this->getStoreable($this->fetched))));
    }

    /**
     * Applies any callback has been defined for given property.
     * 
     * 
     * @param   string      $property_name
     * @param   string      $from
     */
    private function getCallbackValue($property_name)
    {

        $property = $this->getThisEntity()->getProperty($property_name);
        if ($property->getDerived()) {
            $callback = $property->getDerived()->getCallback();
            return $this->getThisEntity()->callMethod($this->getInstance(), $callback, []);
        }
    }

    /**
     * Saves entity details to database.
     * It inserts entity to database if you have newly created entity or it 
     * updates entity to database if its already been there.
     * Returns newly inserted identity value if there is any defined in entity.
     * 
     * @return int
     * @throws \Nishchay\Exception\ApplicationException
     */
    public function save()
    {
        if ($this->isEntityUpdateable === false) {
            throw new ApplicationException('Entity is readonly.', 1, null, 911068);
        }

        # Entity is considered as insertable if this entity does not contain
        # values from databse. We will insert record into database only if
        # entity is insertable otherwise we will update..
        if ($this->isInsertable()) {
            return $this->insert();
        }
        # For this case we will update only updated values to database table.
        # Update query may use idenity value if it exists otherwise we will use
        # fetched values in where caluse.
        else {
            return $this->update();
        }
    }

    /**
     * Removes values.
     * If this entity is fetched from database then record is removed by
     * either identity or fetched values(Fetched value results in where clause).
     * 
     * @return type
     * @throws \Nishchay\Exception\ApplicationException
     */
    public function remove()
    {
        if ($this->isEntityUpdateable === false) {
            throw new ApplicationException('Entity is readonly.', 1, null, 911069);
        }
        $query = $this->getThisEntity()->getQuery();

        # Entity record can also be removed by setting all properties to value.
        # These values with properties results in where clause.
        $this->setCondition($query);


        # If there are any callback to be executed before removing records, we
        # will execute. if any of callback returns false, will cancel remove 
        # operation and return false indicating that remove got cancelled.
        if ($this->executeBeforeChange(self::REMOVE) === false) {
            throw new ApplicationException('Entity [' . $this->entityClass . ']'
                    . ' record can not be removed as before change event'
                    . ' return failure.', 1, null, 911070);
        }

        # Executing remove query.
        $result = $query->remove();

        # Executing after remove event.
        $this->executeAfterChange(self::REMOVE);

        # Reseting this class to their default value.
        $this->flush();
        $this->fetched = [];
        return $result;
    }

    /**
     * Enable or disable skip relative value verification.
     * 
     * @param bool $flag
     * @return $this
     */
    public function skipRelativeValidation(bool $flag = true)
    {
        $this->skipRelativeValidation = $flag;
        return $this;
    }

    /**
     * Insets record into database table.
     * 
     * @return int|booean
     */
    public function insert()
    {

        # If there are any callback to be executed before inserting records, 
        # we will fire callback. if any of callback returns false, will cancel 
        # insert operation and return with false to indicate that insert got 
        # cancelled.
        if ($this->executeBeforeChange(self::INSERT) === false) {
            throw new ApplicationException('Entity [' . $this->entityClass . ']'
                    . ' record can be inserted as before change event return'
                    . ' failure.', 1, null, 911071);
        }
        
        # We first must validate each property of entity class before we proceed
        # for anything. Below method will return updated properties with its 
        # value. We will these property query builder for update.
        $passed = $this->getThisEntity()
                ->validateEntityRecord($this->getInstance(), $this->getInstanceOfFetched(), $this->skipRelativeValidation);

        # Will not proceed if nothing has been updated.
        if (empty($passed)) {
            return false;
        }

        $query = $this->getThisEntity()->getQuery();
        $query->setColumnWithValue($this->getStoreable($passed, $query));

        # We should add extra property to query builder if any of extra 
        # property added, update or removed.
        $this->addExtraPropertyToQuery($query);

        $result = $query->insert();
        $this->executeAfterChange(self::INSERT);

        $identity = $this->getThisEntity()
                ->getIdentityProperty();

        # If class has identity property and it's not set, we should update it
        # with newly generated value.
        if ($identity !== false && $identity
                        ->getValueFromEntity($this->getInstance()) === null) {
            $identity->updateValueToEntity($result, $this->getInstance());
        }

        # Reflect config will convert enitty record mode to update form insert.
        $this->reflectConfig();

        return $result;
    }

    /**
     * Returns TRUR if extra property added, updated or removed.
     * 
     * @return boolean
     */
    private function isExtraPropertyUpdated()
    {
        # We will directly return true if count of original extra properties(
        # properties which were fetched from database) is differing from extra 
        # property set to this entity.
        if (count($this->extraOriginal) !== count($this->extraProperty)) {
            return true;
        }

        # Now we have to find out that if any extra property added to or 
        # removed from this entity. We can find this by matching keys of 
        # original extra property(properties which are fetched from database) 
        # and extra property set to this entity. 
        if (!empty(array_diff_key($this->extraOriginal, $this->extraProperty))) {
            return true;
        }

        # So till now nothing has been added and removed. But still need to 
        # check that if any exisiting extra property has been updated not.
        foreach ($this->extraOriginal as $name => $value) {
            if ($value !== $this->extraProperty[$name]['value']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validates updated property.
     * 
     * @param array $updated
     */
    private function validateUpdated($updated)
    {
        foreach ($updated as $name => $value) {
            $property = $this->getThisEntity()->getProperty($name);
            $property->validate($this->getInstanceOfFetched(), $value);

            # If the property is relative to property  of another class, we
            # should verify that value belongs to that property.
            if ($this->skipRelativeValidation === false) {
                $property->getRelative() &&
                                $this->getThisEntity()
                                ->validateRelative($property, $value);
            }
        }
    }

    /**
     * Forces entities to be updated.
     * 
     * @param boolean
     */
    public function update()
    {
        # First we will check if any of the entity property is updated or 
        # not. We will also check if any extra property added, removed or 
        # updated. If nothing is updated, we will not fire update query.
        $updated = $this->getUpdated();
        if (empty($updated) &&
                $this->isExtraPropertyUpdated() === false) {
            return false;
        }

        $beforeEventCall = Coding::serialize($this->getInstance());
        # If there are any callback to be executed before updating records, we
        # will execute. if any of callback returns false, will cancel update 
        # operation and return FASLE indicating that update got cancelled.
        if ($this->executeBeforeChange(self::UPDATE, array_keys($updated)) === false) {
            throw new ApplicationException('Entity [' . $this->entityClass . ']'
                    . ' record can not be updated as before change event'
                    . ' return failure.', 1, null, 911072);
        }

        $afterEventCall = Coding::serialize($this->getInstance());

        # Let's fetch again, this is to consider entity property updated by event.
        if ($beforeEventCall !== $afterEventCall) {
            $updated = $this->getUpdated();
            if (empty($updated) &&
                    $this->isExtraPropertyUpdated() === false) {
                return false;
            }
        }

        $this->validateUpdated($updated);

        $query = $this->getReflectiveQuery($updated);

        $result = $this->setCondition($query)->update();
        $this->executeAfterChange(self::UPDATE, array_keys($updated));
        $this->reflectConfig();
        return $result;
    }

    /**
     * 
     * @param type $entity
     * @return type
     */
    private function getNonEmpty()
    {
        $values = $this->getThisEntity()
                ->getPropertyValues($this->getInstance());
        foreach ($values as $name => $value) {
            if (empty($values[$name])) {
                unset($values[$name]);
            }
        }
        return $values;
    }

    /**
     * Executes callback to be executed before persisting any changes of entity
     * to database.
     * 
     * @param   string      $mode
     * @return  boolean
     */
    private function executeBeforeChange($mode, $updatedNames = null)
    {
        return $this->getThisEntity()
                        ->executeBeforeChange($this->getConstantEntity($this->getInstanceOfFetched()), $this->getInstance(), $mode, $updatedNames);
    }

    /**
     * Returns instance of ConstantEntity of passed entity class instance.
     * 
     * @param object $instance
     * @return ConstantEntity
     */
    private function getConstantEntity($instance)
    {
        return new ConstantEntity($this->getThisEntity()
                        ->getPropertyValues($instance, true), $this->entityClass);
    }

    /**
     * Executes callback to be executed after persisting any changes of entity 
     * to database.
     * 
     * @param   string        $mode
     * @return  boolean
     */
    private function executeAfterChange($mode, $updatedNames = null)
    {
        return $this->getThisEntity()
                        ->executeAfterChangeEvent($this->getInstanceOfFetched(), $this->getInstance(), $mode, $updatedNames);
    }

    /**
     * Returns entity class instance with it's property value from fetched data.
     * 
     * @return object
     */
    public function getInstanceOfFetched()
    {
        if ($this->fetchedInstance !== null) {
            return $this->fetchedInstance;
        }

        $this->fetchedInstance = $this->getThisEntity()->getReflectionClass()
                ->newInstance();
        foreach ($this->fetched as $name => $value) {
            $this->getThisEntity()->getProperty($name)
                    ->updateValueToEntity($value, $this->fetchedInstance);
        }
        return $this->fetchedInstance;
    }

    /**
     * Returns query instance for this enitty after setting properties which
     * are updated.
     * 
     * @return \Nishchay\Data\Query
     */
    private function getReflectiveQuery($updated)
    {
        $query = $this->getThisEntity()->getQuery();
        return $this->addExtraPropertyToQuery(
                        $query->setColumnWithValue($this->getStoreable($updated, $query))
        );
    }

    /**
     * Adds extra properties to insert or update.
     * 
     */
    private function addExtraPropertyToQuery(Query $query)
    {
        # If extra property added, updated or removed, we will serialize extra
        # property detail to be store in extra property column of this entity. 
        if ($this->isExtraPropertyUpdated()) {
            $query->setColumnWithValue(Property::EXTRA_PROPERTY, Coding::serialize($this->extraProperty, true));
        }

        return $query;
    }

    /**
     * Returns updated values with it's name.
     * 
     * @return array
     */
    private function getUpdated()
    {
        $updated = [];
        foreach ($this->getThisEntity()
                ->getProperties() as $name) {

            # We will not take properties which were not fetched from database.
            # This is to allow developer to update only properties which were
            # fetched.
            if (!array_key_exists($name, $this->fetched)) {
                continue;
            }

            # Value fetched from database.
            $value = $this->fetched[$name];

            # Value from enitty instnace.
            $entityValue = $this->getPropertyValue($name);

            # Now we will first apply setter method to fetched value if there 
            # is any setter method and then we will check fetched value is 
            # different from entity value or not.
            if ($this->isDifferent($this->getThisEntity()
                                    ->getProperty($name)
                                    ->applySetter($value), $entityValue)) {

                # Here we are checking that updated value is different from 
                # fetched value or not.
                if ($this->isDifferent($value, $entityValue) !== true) {
                    continue;
                }

                $updated[$name] = $entityValue;
            }
        }

        return $updated;
    }

    /**
     * Returns true if both values are different.
     * 
     * @param   mixed       $value1
     * @param   mixed       $value2
     * @return  boolean
     */
    private function isDifferent($value1, $value2)
    {
        return $this->getStringValue($value1) !== $this->getStringValue($value2);
    }

    /**
     * Returns scaler value. If value is an object then it returns serialized
     * value.
     * 
     * @param mixed $value
     * @return string
     */
    private function getStringValue($value)
    {
        return is_scalar($value) ? $value : Coding::serialize($value, true);
    }

    /**
     * Converts all data to string form.
     * object and array are serialized to get string form.
     * 
     * @param   string|array    $data
     * @return  array
     */
    private function getStoreable($data, $query = null)
    {
        if (!is_array($data)) {
            return $this->getStoreable([$data], $query);
        }

        foreach ($data as $key => $value) {
            $property = $this->getThisEntity()->getProperty($key);
            if (!is_scalar($value)) {
                $value = $property ?
                        $property->getScalerValue($value) : Coding::serialize($value, true);
            } else if (is_bool($value)) {
                $value = $value ? 1 : 0;
            }
            if ($property && $query &&
                    $property->isDerived() === false &&
                    $property->getDatatype()->getEncrypt()) {
                if ($this->isDBEncryption()) {
                    unset($data[$key]);
                    $key .= Query::AS_IT_IS;
                }
                $value = $this->getEncrypter($query)->encrypt($value);
            }
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * Applies condition to query based on value of fetched or identity 
     * property.
     * 
     * @param   \Nishchay\Data\Query     $query
     * @return  \Nishchay\Data\Query
     */
    private function setCondition(Query $query)
    {
        $identity = $this->getIdentityValue();
        if (!empty($identity)) {
            $query->setCondition($this->getThisEntity()->getIdentity(), $identity);
        } else {
            $values = !empty($this->fetched) ?
                    $this->fetched : $this->getNonEmpty();
            $query->setCondition($this->getStoreable($values, $query));
        }
        return $query;
    }

    /**
     * Returns identity value if entity has identity property otherwise 
     * returns false.
     * 
     * @return mixed
     */
    public function getIdentityValue()
    {
        if (($identity = $this->getThisEntity()
                ->getIdentityProperty()) !== false) {
            return $identity->getValueFromEntity($this->getInstance());
        }
        return false;
    }

    /**
     * Returns Entity Annotation Class instance.
     * 
     * @return  \Nishchay\Data\Annotation\EntityClass
     */
    private function getThisEntity($name = null)
    {
        return $this->entity($name === null ?
                        $this->entityClass : $name);
    }

    /**
     * Saves all updated static properties to database.
     * 
     * @param   string      $name
     * @param   string      $originalValue
     * @return  boolean
     */
    public function saveStatic()
    {
        foreach ($this->getThisEntity()
                ->getStaticProperties() as $propertyName => $originalValue) {


            $property = $this->getThisEntity()->getProperty($propertyName);

            # No need to pass instnace to fetch value of static property.
            $entityValue = $property->getReflectionProperty()->getValue();
            $property->validate($this->getInstance(), $entityValue);

            # There can be three kind of values.
            # 1. Derived from table(originalValue).
            # 2. Value after applying setter(afterSetter).
            # 3. Value which have been changed after setting to
            #    entity(entityValue).
            $afterSetter = $originalValue === false ?
                    null : $property->applySetter($originalValue);

            # We will check that entityValue is different from afterSetter and
            # originalValue. If entityValue is different we will persist in
            # table.
            if ($this->isDifferent($afterSetter, $entityValue)) {
                if ($this->isDifferent($originalValue, $entityValue) === false) {
                    continue;
                }

                $query = new Query($this->entityConnection);
                $query->setTable(Entity::STATIC_TABLE_NAME)
                        ->setColumnWithValue([
                            'data' => current($this
                                    ->getStoreable([$entityValue], $query))
                ]);
                $combination = [
                    'entityClass' => $this->entityClass,
                    'propertyName' => $propertyName
                ];
                $originalValue === false ?
                                $query->setColumnWithValue($combination)
                                        ->insert() :
                                $query->setCondition($combination)
                                        ->update();
            }
        }
    }

    /**
     * Returns all extra property of current record.
     * 
     * @return array
     */
    public function getExtraProperties()
    {
        $extra = [];
        foreach ($this->extraProperty as $key => $value) {
            $extra[$key] = $value['value'];
        }
        return $extra;
    }

    /**
     * Returns true if entity record has at least one extra property.
     * 
     * @return boolean
     */
    public function isExtraPropertyAdded()
    {
        return !empty($this->extraProperty);
    }

    /**
     * Returns true if entity record has given extra property.
     * Extra property can also be checked by using isset method on the object
     * with extra property name.
     * 
     * @param   string      $name
     * @return  boolean
     */
    public function isExtraPropertyExists($name)
    {
        return array_key_exists($name, $this->extraProperty);
    }

    /**
     * Removes extra property.
     * 
     * @param type $name
     * @return boolean
     * @throws ApplicationException
     */
    public function removeExtraProperty($name)
    {
        if ($this->isExtraPropertyExists($name) === false) {
            throw new ApplicationException('Extra property [' .
                    $this->entityClass . '::' . $name . '] does not exists.', 1, null, 911073);
        }

        unset($this->extraProperty[$name]);

        return true;
    }

    /**
     * Returns Entity Class name.
     * 
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityClass;
    }

    /**
     * Returns entity table to which it is associated.
     * 
     * @return string
     */
    public function getEntityTable()
    {
        return $this->entityTable;
    }

    /**
     * Returns all properties including extra properties.
     * 
     * @param type $as
     * @return array|\stdClass
     */
    public function getData($as = 'object', $fetched = true, $withNull = true)
    {
        $values = array_merge($this->getThisEntity()
                        ->getPropertyValues($this->getInstance(), (bool) $fetched), $this->getExtraProperties());

        if ($withNull === false) {
            foreach ($values as $key => $val) {
                if ($val === null) {
                    unset($values[$key]);
                }
            }
        }

        return $as === 'object' ? ((object) $values) : $values;
    }

    /**
     * Returns temporary value.
     * It throws warning if temporary value not found and error flagged to show.
     * 
     * @param   string      $name
     * @param   boolean     $error
     * @return  mixed
     */
    public function getTemp(string $name)
    {
        if ($this->isTempExists($name) === false) {
            throw new ApplicationException('Temporary data [' . $name . ']'
                    . ' does not exist.', 1, null, 911074);
        }
        return $this->tempData[$name];
    }

    /**
     * Returns TRUE if temp data with $name exists.
     * 
     * @param string $name
     * @return bool
     */
    public function isTempExists(string $name): bool
    {
        return array_key_exists($name, $this->tempData);
    }

    /**
     * Sets temporary value.
     * 
     * @param   string  $name
     * @param   mixed   $value
     */
    public function setTemp(string $name, $value)
    {
        $this->tempData[$name] = $value;
        return $this;
    }

    /**
     * Remove data with $name from temp data. It returns false if data does not
     * exists in temp data.
     * 
     * @param string $name
     */
    public function removeTemp(string $name): bool
    {
        if ($this->isTempExists($name) === false) {
            return false;
        }

        unset($this->tempData[$name]);
        return true;
    }

    /**
     * Returns instance of EntityQuery.
     * 
     * @return  \Nishchay\Data\EntityQuery
     */
    public function getEntityQuery($properties = true, $withJoin = false)
    {
        $builder = $this->getSelectiveQuery(false, true);
        if ($properties === true) {
            $properties = $this->getSelectiveColumn($this->getThisEntity(), $builder, false);
            $builder->setProperty(array_values($properties));
            if ($withJoin) {
                foreach ($this->getThisEntity()->getDerivedProperties() as $propertyName => $no) {
                    $derived = $this->getThisEntity()->getProperty($propertyName)->getDerived();
                    if ($derived->getHold() !== ResolvedJoin::HOLD_TYPE_ARRAY) {
                        $builder->setDerivedProperty($propertyName);
                    }
                }
            }
        } else if (is_array($properties)) {
            foreach ($properties as $name) {
                if (strpos($name, '.') === false) {
                    $name = StringUtility::getExplodeLast('\\', $this->entityClass) . '.' . $name;
                }
                $builder->setProperty($name);
            }
        }
        return $builder;
    }

    /**
     * Executes query and returns results in the form entity instances.
     *  
     * @param   \Nishchay\Data\EntityQuery         $query
     * @return  \Nishchay\Data\Query
     */
    public function fetchByEntityQuery(EntityQuery $query)
    {
        # We will first fetch records from database then assign it to
        # Entity class's property.
        $records = $query->getQueryBuilder()->get();

        # Below call gives us enity classes which has at least one property was
        # set to assign to entity class. Suppose developer has used one class
        # for join only and no property is set to fetch then this enitty class
        # will not return in below call.
        $mapping = $query->getReturnableEntity();
        $iterator = [];
        foreach ($records as $row) {
            $value = $this->getRowAsEntity($row, $mapping, $query);
            foreach ($query->getDerivingProperties() as $name) {
                list($alias, $property) = explode('.', $name);
                if (isset($mapping[$alias]) === false) {
                    continue;
                }
                $toSet = $value;
                if ($value instanceof EntityManager) {
                    $toSet = $value;
                } else if (isset($value->{$alias})) {
                    $toSet = $value->{$alias};
                } else {
                    continue;
                }
                $entity = $this->entity($mapping[$alias]);
                $toSet->setDerivedPropertyValues($property, $entity->getDerivedProperty($property), $row, $entity->getProperty($property), $entity->getJoinTable($property));
            }
            $iterator[] = $value;
        }
        return new DataIterator($this->processLazyProperties($iterator));
    }

    /**
     * Updates entity values by using entity query.
     * 
     * @param EntityQuery $entityQuery
     * @return int
     */
    public function updateByEntityQuery(EntityQuery $entityQuery)
    {
        $properties = $entityQuery->getPropertyWithValue();
        foreach ($properties as $name => $value) {
            $this->{$name} = $value;
        }
        $this->validateUpdated($properties);

        $query = $entityQuery->getQueryBuilder();
        $query->setColumnWithValue($this->getStoreable($properties, $query));

        # If there are any callback to be executed before updating records, we
        # will fire. if any of callback returns false, will cancel update 
        # operation and return false indicating that update got cancelled.
        if ($this->executeBeforeChange(self::UPDATE) === false) {
            return false;
        }

        $updated = $query->update();

        $this->executeAfterChange(self::UPDATE);

        return $updated;
    }

    /**
     * 
     * @param type $row
     * @param type $mapping
     * @param type $query
     * @return type
     */
    private function getRowAsEntity($row, $mapping, $query)
    {
        # If one row contains more than one entity then row will contain
        # all alias with it's value as entity instance otherwise it contains
        # enitty instance itself.
        $toReturn = new stdClass;

        # Iterating over each mapping. Here $mapping is alias name and 
        # $class is entity class name.
        foreach ($mapping as $alias => $class) {

            $isAllNull = true;

            $object = new static($class);
            foreach ($object->getThisEntity()->getProperties() as $propertyName) {
                if (isset($row->{$propertyName}) && $row->{$propertyName} !== null) {
                    $isAllNull = false;
                    break;
                }
            }

            # If all value of entity is null then we do have to assign entity.
            if ($isAllNull) {
                $toReturn->{$alias} = null;
                continue;
            }

            # Making this entity not to assign derived properties.
            $object->enableDerived(false);
            $object->enableLazy(false);

            # Creating clone of the fetched row so that any modification
            # do not affect original row.
            $fetchable = clone $row;

            # Now we will remove properties which are unfetchable.
            foreach ($query->getUnFetchable($alias) as $notToFetch) {
                unset($fetchable->{$notToFetch});
            }

            $object->setPropertyValues($fetchable);
            $object->isEnityReturned = true;

            $toReturn->{$alias} = $object;
        }
        # Row will contain entity instance itself in the case of only entity
        # is returned.
        return count($mapping) === 1 ?
                $toReturn->{$alias} : $toReturn;
    }

    /**
     * Returns all properties of Entity class.
     * 
     * @return array
     */
    private function getProperties()
    {
        return $this->getThisEntity()
                        ->getPropertyRules();
    }

    /**
     * Returns Database manager builder configured with entity class.
     * 
     * @return \Nishchay\Data\DatabaseManager
     */
    public function getDatabaseManager()
    {
        # To fetch exists table structure.
        $meta = new MetaTable($this->entityTable);


        $dbManager = new DatabaseManager($this->entityConnection);
        $dbManager->setTableName($this->entityTable);
        $columns = [];

        # Listing all columns aleady exists in table so that we can later decide
        # whether column need to be changed.
        foreach ($meta->getColumns() as $col) {
            $columns[] = $col->name;
        }
        $foregins = [];

        # Listing all foreign key defined on table so that we can later add
        # foregin key only if it already exists in table.
        foreach ($meta->getForeignKeys() as $col) {
            $foregins[$col->column_name] = $col;
        }

        $identityPropertyName = false;
        if ($this->getThisEntity()->getIdentityProperty()) {
            $identityPropertyName = $this->getThisEntity()->getIdentityProperty()->getPropertyName();
        }

        foreach ($this->getProperties() as $name => $property) {
            if ($property->getDerived() !== false) {
                continue;
            }
            $columnName = [$name => $name];
            if (!in_array($name, $columns)) {
                $columnName = $name;
            }
            $dataType = $property->getDataType();
            $dbManager->setColumn($columnName, $dbManager->getType($dataType->getType(), $dataType->getLength()), ($dataType->getRequired() || $identityPropertyName === $name) ? false : null);

            # If property is relative and is using perfect join then we will add foreign key
            # only if it does not already exists in table.
            if (($relative = $property->getRelative()) && empty($foregins[$name]) && $relative->getType() === Query::INNER_JOIN) {

                # Fetching entity so that we can find database table for the entity.
                $entity = $this->entity($relative->getTo());
                $table = $entity->getEntity()->getName();

                # If property is relative to which property of relative entity
                # is not defined then we will use identity property of relative
                # entity.
                if (($foreignColumn = $relative->getName()) === false) {
                    $foreignColumn = $entity->getIdentity();
                }
                $dbManager->addForeignKey($name, ['table' => $table, 'column' => $foreignColumn]);
            }
        }

        # If table does not have primary key we will add identity property as
        # primary key of table.
        if ($meta->getPrimaryKey() === false && $identityPropertyName !== false) {
            $dbManager->setPrimaryKey($identityPropertyName);
        }
        return $dbManager;
    }

    /**
     * 
     * @return type
     */
    public function __debugInfo()
    {
        return $this->getData('array', false);
    }

}
