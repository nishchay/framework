<?php

namespace Nishchay\Data\Annotation\Property;

use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use ReflectionProperty;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Data\Annotation\Property\DataType;
use Nishchay\Data\Annotation\Property\Derived;
use Nishchay\Data\Annotation\Property\Relative;
use Nishchay\Data\Annotation\Property\Validation;
use Nishchay\Utility\Coding;
use Nishchay\Utility\ArrayUtility;
use DateTime;
use Nishchay\Processor\VariableType;
use Nishchay\Security\Encrypt\EncryptTrait;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\Utility\DateUtility;

/**
 * Property annotation class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class Property extends BaseAnnotationDefinition
{

    use EncryptTrait,
        MethodInvokerTrait;

    /**
     * Name for extra property column.
     */
    const EXTRA_PROPERTY = 'extraProperty';

    /**
     * Flag of identity property.
     * 
     * @var boolean 
     */
    private $identity = false;

    /**
     * DataType annotation.
     * 
     * @var \Nishchay\Data\Annotation\Property\DataType 
     */
    private $datatype = false;

    /**
     * Value generator type.
     * 
     * @var string 
     */
    private $generator = false;

    /**
     * Derived annotation.
     * 
     * @var \Nishchay\Data\Annotation\Property\Derived 
     */
    private $derived = false;

    /**
     * Relative annotation.
     * 
     * @var \Nishchay\Data\Annotation\Property\Relative 
     */
    private $relative = false;

    /**
     * Validation annotation.
     * 
     * @var \Nishchay\Data\Annotation\Property\Validation 
     */
    private $validation = false;

    /**
     * Property name.
     * 
     * @var string 
     */
    private $propertyName;

    /**
     * Property type.
     * 
     * @var string 
     */
    private $propertyType = 'fetch';

    /**
     * Reserved column names.
     * 
     * @var array 
     */
    private $reserved = [self::EXTRA_PROPERTY];

    /**
     * Property's value. Applicable only if property is static.
     * 
     * @var mixed
     */
    private $propertyValue;

    /**
     * 
     * @param   string      $class
     * @param   string      $method
     * @param   array       $annotation
     */
    public function __construct($class, $method, $property_name, $annotation)
    {
        parent::__construct($class, $method);
        $this->propertyName = $property_name;
        $this->isReserved()->init($annotation);
    }

    /**
     * Throws exception is property name is reserved name.
     * 
     * @return  \Nishchay\Data\Annotation\Property\Property
     * @throws  \Nishchay\Exception\NotSupportedException
     */
    private function isReserved()
    {
        # There are some reserved property name which should not be used.
        # So we are preventing here.
        if (in_array(strtolower($this->propertyName), $this->reserved)) {
            throw new NotSupportedException('[' . $this->class . '::' . $this->propertyName .
                    '] is reserved property name.', $this->class, $this->method, 911021);
        }
        return $this;
    }

    /**
     * Returns instance of ReflectionProperty on entity's property.
     * 
     * @return \ReflectionProperty
     */
    public function getReflectionProperty()
    {
        $reflection = new ReflectionProperty($this->class, $this->propertyName);
        $reflection->setAccessible(true);
        return $reflection;
    }

    /**
     * Initializes.
     * 
     * @param array $annotation
     */
    private function init($annotation)
    {
        $priority = ['identity', 'datatype'];
        $this->setter(ArrayUtility::customeKeySort($annotation, $priority));
    }

    /**
     * Returns TRUE if property is identity property.
     * 
     * @return bool
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Returns DataType annotation if defined otherwise it returns FALSE.
     * 
     * @return DataType
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * Returns generator.
     * 
     * @return mixed
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    /**
     * Returns Derived annotation if defined otherwise it returns FALSE.
     * 
     * @return Derived
     */
    public function getDerived()
    {
        return $this->derived;
    }

    /**
     * Returns TRUE if the property value should be derived.
     * 
     * @return boolean
     */
    public function isDerived()
    {
        return $this->derived === false ? false : true;
    }

    /**
     * Returns Relative annotation if defined otherwise it returns FALSE.
     * 
     * @return Relative
     */
    public function getRelative()
    {
        return $this->relative;
    }

    /**
     * Sets identity flag to true.
     * 
     * @param boolean $identity
     */
    protected function setIdentity($identity)
    {
        $this->identity = true;
    }

    /**
     * Sets DataType annotation.
     * 
     * @param array $datatype
     */
    protected function setDatatype($datatype)
    {
        $this->datatype = new DataType($this->class, null, $this->propertyName, $datatype);
    }

    /**
     * Sets Generator annotation.
     * 
     * @param array $generator
     */
    protected function setGenerator($generator)
    {
        $this->generator = $generator;
    }

    /**
     * Sets Derived annotation.
     * 
     * @param array $derived
     */
    protected function setDerived($derived)
    {
        if ($this->isStatic()) {
            throw new NotSupportedException('Static property'
                    . ' [' . $this->class . '::' . $this->propertyName . ']'
                    . ' should not derived.', $this->class, $this->method, 911022);
        }

        $this->derived = new Derived($this->class, NULL, $this->propertyName, $derived);
        $this->propertyType = $this->derived->getDerivedType();
    }

    /**
     * Sets Relative annotation.
     * 
     * @param array $relative
     */
    public function setRelative($relative)
    {
        $this->relative = new Relative($this->class, NULL, $this->propertyName, $relative);
    }

    /**
     * Returns Property type.
     * 
     * @return stirng
     */
    public function getPropertyType()
    {
        return $this->propertyType;
    }

    /**
     * Returns property name.
     * 
     * @return type
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Converts string value to type as defined in entity class.
     * 
     * @param string $value
     * @return mixed
     */
    public function getValue($value, $propertyName = null)
    {
        if ($this->getDerived() !== false) {
            $dataType = $this->getDerived()
                    ->getDatatype($propertyName);
        } else {
            $dataType = $this->getDatatype();
        }

        if ($dataType->getEncrypt() && $this->isDBEncryption() === false) {
            $value = $this->getEncrypter()->decrypt($value);
        }

        switch ($dataType->getType()) {
            case VariableType::INT:
                return (int) $value;
            case VariableType::DOUBLE:
            case VariableType::FLOAT:
                return doubleval($value);
            case VariableType::STRING:
                return (string) $value;
            case VariableType::BOOLEAN:
                if (is_bool($value) || $value === null) {
                    return $value;
                } else if (in_array((int) $value, [0, 1])) {
                    return (int) $value === 1;
                }
                return null;
            case VariableType::DATE:
            case VariableType::DATETIME:
                return !empty($value) ? (new DateTime($value)) : null;
            case VariableType::MIXED:
                return Coding::isUnSerializable($value) ? Coding::unserialize($value) : $value;
            case VariableType::DATA_ARRAY:
            default:
                return Coding::isUnSerializable($value) ? Coding::unserialize($value) : null;
        }
    }

    /**
     * Returns scaler value to be stored to database.
     * If value is instance of DateTime class then it will return date formated
     * as per property data type defined in entity class otherwise it will 
     * return serialized value.
     * 
     * @param object $value
     * @return type
     */
    public function getScalerValue($value)
    {
        if (is_scalar($value)) {
            return $value;
        }

        if ($value instanceof DateTime) {
            $format = DateUtility::MYSQL_DATETIME_FORMAT;
            if ($this->getDatatype()->getType() === VariableType::DATE) {
                $format = DateUtility::MYSQL_DATE_FORMAT;
            }
            return $value->format($format);
        }

        return Coding::serialize($value);
    }

    /**
     * May call setter method and returns updated/actual 
     * value based on setter called or not.
     * 
     * @param   mixed       $value
     */
    public function applySetter($value)
    {
        $methodName = $this->isPropertySetterExist($this->propertyName);
        if ($methodName !== false) {
            $class = $this->class;
            return call_user_func([new $class, $methodName], $value);
        }

        return $value;
    }

    /**
     * Returns name of setter if it exists other it reutrns FALSE.
     * 
     * @return  boolean|string
     */
    private function isPropertySetterExist()
    {
        $method_name = 'set' . ucfirst($this->propertyName);
        if (method_exists($this->class, $method_name)) {
            return $method_name;
        }
        return FALSE;
    }

    /**
     * Returns instance Validation annotation.
     * 
     * @return Validation
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Sets validation anotation.
     * 
     * @param array $validation
     */
    protected function setValidation($validation)
    {
        $this->validation = new Validation($this->class, $this->method, $validation);
    }

    /**
     * Returns TRUE if property is static.
     * 
     * @return boolean
     */
    public function isStatic()
    {
        return $this->getReflectionProperty()->isStatic();
    }

    /**
     * Returns TRUE if property is public.
     * 
     * @return boolean
     */
    public function isPublic()
    {
        return $this->getReflectionProperty()->isPublic();
    }

    /**
     * Returns static property's value.
     * It always returns NULL, if property is non static.
     * 
     * @return type
     */
    public function getPropertyValue()
    {
        return $this->propertyValue;
    }

    /**
     * Sets property's value only if property is static.
     * 
     * @param string $propertyValue
     */
    public function updateStaticPropertyValue($propertyValue)
    {
        if ($this->isStatic() === false) {
            throw new NotSupportedException('Property [' . $this->class . '::'
                    . $this->propertyName . '] is not static.', $this->class, $this->method, 911023);
        }
        $this->propertyValue = $propertyValue;
        $this->updateValueToEntity($propertyValue, NULL);
    }

    /**
     * Sets value to instance.
     * 
     * @param type $value
     * @param type $instance
     */
    public function updateValueToEntity($value, $instance)
    {
        $this->getReflectionProperty()->setValue($instance, $value);
    }

    /**
     * Returns value of property from given entity instance.
     * 
     * @param type $instance
     * @return type
     */
    public function getValueFromEntity($instance)
    {
        return $this->getReflectionProperty()
                        ->getValue($instance);
    }

    /**
     * Validates property's value as per its data type and validation callback
     * if set.
     * 
     * @param   object                                          $instance
     * @param   mixed                                           $value
     * @throws  \Nishchay\Exception\ApplicationException
     */
    public function validate($instance, $value)
    {
        if ($this->isDerived()) {
            throw new ApplicationException('Derived property [' . $this->class .
                    '::' . $this->propertyName . '] can not be updated.', $this->class, $this->method, 911024);
        }

        $this->getDatatype()
                ->validate($value, $this->getValueFromEntity($instance) !== null);
        $this->validateFromValidationCallback($value);
    }

    /**
     * Calls validation callback if defined on property.
     * 
     * @param type $value
     * @throws ApplicationException
     */
    private function validateFromValidationCallback($value)
    {
        $validation = $this->getValidation();
        if ($validation !== false) {
            $callback = $validation->getCallback();
            if ($this->invokeMethod([new $callback['class'], $callback['method']], [$value]) !== true) {
                throw new ApplicationException('Value of property [' .
                        $this->class . '::' . $this->propertyName . '] is not valid as'
                        . ' it could not pass validation defined in [' .
                        implode('::', $callback) . '].', $this->class, $this->method, 911025);
            }
        }
    }

}
