<?php

namespace Nishchay\Data\Annotation\Property;

use Exception;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use DateTime;
use Nishchay\Annotation\BaseAnnotationDefinition;
use Nishchay\Exception\InvalidAnnotationParameterException;
use Nishchay\Processor\VariableType;
use Nishchay\Utility\ArrayUtility;
use Nishchay\Utility\MethodInvokerTrait;

/**
 * Annotation class for Data type of entity. 
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class DataType extends BaseAnnotationDefinition
{

    use MethodInvokerTrait;

    /**
     * Predefined data types.
     * 
     * @var array 
     */
    private static $predefinedTypes = [
        VariableType::INT, VariableType::FLOAT, VariableType::DOUBLE,
        VariableType::STRING, VariableType::DATE, VariableType::DATETIME,
        VariableType::DATA_ARRAY, VariableType::MIXED, VariableType::BOOLEAN
    ];

    /**
     * Type parameter.
     * 
     * @var string 
     */
    private $type = false;

    /**
     * Length parameter.
     * 
     * @var double 
     */
    private $length = null;

    /**
     * Property is required or not.
     * 
     * @var boolean 
     */
    private $required = false;

    /**
     * Property is read only or not.
     * 
     * @var boolean 
     */
    private $readonly = false;

    /**
     * How many maximum time value is allowed to repeat.
     * 
     * @var type 
     */
    private $repeat = 0;

    /**
     * Property name on which annotation is defined.
     * 
     * @var string 
     */
    private $propertyName;

    /**
     * Flag to indicate whether value need encryption.
     * 
     * @var boolean
     */
    private $encrypt = false;

    /**
     * List of supported values.
     * 
     * @var array 
     */
    private $values = false;

    /**
     * Default value.
     * 
     * @var string
     */
    private $default = null;

    /**
     * Initializes.
     * 
     * @param   string  $class
     * @param   string  $method
     * @param   array   $parameter
     */
    public function __construct($class, $method, $propertyName, $parameter)
    {
        parent::__construct($class, $method);
        $this->propertyName = $propertyName;
        $parameter = ArrayUtility::customeKeySort($parameter, ['type', 'length', 'enum', 'default']);
        $this->setter($parameter, 'parameter');
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
     * @return double
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * 
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Returns true if property is required otherwise false.
     * 
     * @return boolean
     */
    public function getReadonly()
    {
        return $this->readonly;
    }

    /**
     * 
     * @param string $type
     */
    protected function setType($type)
    {
        $type = strtolower($type);
        #Class should be exist when type is not primitive.
        if (!in_array($type, self::$predefinedTypes) && !class_exists($type)) {
            throw new InvalidAnnotationParameterException('Invalid data type'
                    . ' [' . $type . '] for property [' .
                    $this->class . '::' . $this->propertyName . '].', $this->class, null, 911003);
        }

        if (trim($type, '\\') === DateTime::class) {
            throw new NotSupportedException('Use date or'
                    . ' datetime as data type instead of DateTime class for property'
                    . ' [' . $this->class . '::' . $this->propertyName . ']', $this->class, null, 911004);
        }

        $this->type = $type;
    }

    /**
     * Sets length parameter value.
     * 
     * @param double $length
     */
    protected function setLength($length)
    {
        $this->length = $length === null ? null : doubleval($length);
    }

    /**
     * Sets required flag of the property.
     * 
     * @param boolean $required
     */
    protected function setRequired($required)
    {
        $this->required = (bool) $required;
    }

    /**
     * Sets read only flag of the property.
     * 
     * @param boolean $readonly
     */
    protected function setReadonly($readonly)
    {
        $this->readonly = (bool) $readonly;
    }

    /**
     * Returns how many maximum time value is allowed to repeat.
     * Zero means any number of times.
     * 
     * @return int
     */
    public function getRepeat()
    {
        return $this->repeat;
    }

    /**
     * Sets how many maximum time value is allowed to repeat.
     * Zero means any number of times.
     * 
     * @param int $repeat
     */
    protected function setRepeat($repeat)
    {
        $this->repeat = (int) $repeat;
    }

    /**
     * Returns all pre defined data types.
     * 
     * @return array
     */
    public static function getPreDefinedTypes()
    {
        return self::$predefinedTypes;
    }

    /**
     * Validates data type.
     * 
     * @param mixed $value
     * @param bool $set This value is true if its previous value is not null
     * @return bool
     * @throws ApplicationException
     */
    public function validate($value, $set = false): bool
    {
        if (is_null($value) || (is_string($value) && strlen($value) === 0)) {
            # Throws exception only if property is required.
            if ($this->required) {
                throw new ApplicationException('Property [' . $this->class .
                        '::' . $this->propertyName . '] can not be'
                        . ' null or empty.', $this->class, null, 911005);
            }

            # We still need to check if record was update or not.
            $this->readonly && $this->checkReadonly($set);

            return true;
        }

        # Now we should check that property is readonly or not.
        $this->readonly && $this->checkReadonly($set);

        $this->validatePredefined($value) || $this->validateUserDefined($value);

        if ($this->values !== false && in_array($value, $this->values) === false) {
            throw new ApplicationException('Value of property [' .
                    $this->class . '::' . $this->propertyName . ']'
                    . ' should form values list.', $this->class, null, 911007);
        }

        return true;
    }

    /**
     * This throws error if set is true.
     * 
     * @param bool $set
     * @throws ApplicationException
     */
    private function checkReadonly(bool $set)
    {
        if ($set) {
            throw new ApplicationException('Property [' . $this->class . '::' .
                    $this->propertyName . '] is readonly.', $this->class, null, 911006);
        }
    }

    /**
     * Validates value as per validation rule of prdefined type.
     * 
     * @param type $value
     * @throws Exception
     */
    private function validatePredefined($value)
    {
        # Preadefined? Check validation as per type of property.
        if (in_array($this->type, self::$predefinedTypes)) {
            if ($this->invokeMethod(
                            [$this, 'isValid' . ucfirst($this->type)], [$value]
                    ) === false) {
                throw new ApplicationException('Value of property'
                        . ' [' . $this->class . '::' . $this->propertyName . '] is not'
                        . ' valid or not ' . $this->type . '.', $this->class, null, 911008);
            }
            return true;
        }

        return false;
    }

    /**
     * If value as per user defined property validation rule.
     * 
     * @param $value
     * @throws Exception
     */
    private function validateUserDefined($value)
    {
        # When property is  Object type , it should be instance of 
        # defined class name.
        if (!$value instanceof $this->type) {
            throw new ApplicationException('Value of property [' .
                    $this->class . '::' . $this->propertyName . '] must be instance'
                    . ' of ' . $this->type . '.', $this->class, null, 911009);
        }
    }

    /**
     * Checks for value's character length exceeded limit.
     * 
     * @param int|double $length
     * @return bool
     */
    private function isLengthExceeded($length)
    {
        if ($this->length !== null && $this->length !== doubleval(0) && $this->length < doubleval($length)) {
            throw new ApplicationException('Value Length exceeded for'
                    . ' [' . $this->class . '::' . $this->propertyName . '].', $this->class, null, 911010);
        }
        return true;
    }

    /**
     * Returns true if value is string.
     * 
     * @param   string      $value
     * @return  boolean
     */
    private function isValidString($value)
    {
        return is_string($value) && $this->isLengthExceeded(strlen($value));
    }

    /**
     * Returns true if value is integer.
     * 
     * @param   int         $value
     * @return  boolean
     */
    private function isValidInt($value)
    {
        return is_int($value);
    }

    /**
     * Returns true if value is double.
     * 
     * @param   double $value
     * @return  boolean
     */
    private function isValidFloat($value)
    {
        return $this->isValidDouble($value);
    }

    /**
     * Returns true if value is double.
     * 
     * @param   double      $value
     * @return  boolean
     */
    private function isValidDouble($value)
    {
        return is_int($value) || is_double($value);
    }

    /**
     * Returns true if value is valid date.
     * 
     * @param   stirng      $value
     * @return  boolean
     */
    private function isValidDate($value)
    {
        return $value instanceof DateTime;
    }

    /**
     * 
     * @param   string      $value
     * @return  boolean
     */
    private function isValidDatetime($value)
    {
        return $this->isValidDate($value);
    }

    /**
     * Returns true if valid is array.
     * 
     * @param   array       $value
     * @return  boolean
     */
    private function isValidArray($value)
    {
        return is_array($value);
    }

    /**
     * Always returns true.
     * 
     * @return  boolean
     */
    private function isValidMixed()
    {
        return true;
    }

    /**
     * Returns TRUE if $value is valid boolean value.
     * 
     * @param bool $value
     * @return bool
     */
    private function isValidBoolean($value)
    {
        return is_bool($value);
    }
    
    /**
     * Returns TRUE if $value is valid bool value.
     * 
     * @param bool $value
     * @return bool
     */
    private function isValidBool($value)
    {
        return $this->isValidBoolean($value);
    }

    /**
     * Returns true if data type is primitive.
     * 
     * @return boolean
     */
    public function isPrimitive()
    {
        return in_array($this->type, self::$predefinedTypes);
    }

    /**
     * Returns true if property value needed to be encrypted.
     * 
     * @return boolean
     */
    public function getEncrypt()
    {
        return $this->encrypt;
    }

    /**
     * 
     * @param type $encrypt
     * @return $this
     */
    protected function setEncrypt($encrypt)
    {
        $this->encrypt = (boolean) $encrypt;
        return $this;
    }

    /**
     * Returns values list.
     * 
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Returns default value.
     * 
     * @return mixed
     */
    public function getDefault()
    {
        if ($this->default === null) {
            return null;
        }

        if (!in_array($this->type, [VariableType::DATE, VariableType::DATETIME])) {
            return $this->default;
        }

        return DateTime::createFromFormat('U', strtotime($this->default));
    }

    /**
     * Sets values list.
     * 
     * @param array $values
     * @return $this
     */
    protected function setValues($values)
    {
        if (!in_array($this->type, self::$predefinedTypes) ||
                in_array($this->type, [VariableType::BOOLEAN, VariableType::DATE, VariableType::DATETIME, VariableType::DATA_ARRAY, VariableType::MIXED])) {
            throw new NotSupportedException('Values parameter not supported for'
                    . ' data type [' . $this->type . '] for property'
                    . ' [' . $this->class . '::' . $this->propertyName .
                    '].', $this->class, null, 911011);
        }
        $values = (array) $values;
        foreach ($values as $key => $value) {
            try {
                $value = $this->convertDataType($value);
                $this->validatePredefined($value);
                $values[$key] = $value;
            } catch (Exception $e) {
                throw new ApplicationException('Invalid item in values parameter for property'
                        . ' [' . $this->class . '::' . $this->propertyName .
                        '].', $this->class, null, 911012);
            }
        }
        $this->values = $values;
        return $this;
    }

    /**
     * Sets default value.
     * 
     * @param string $default
     * @return $this
     */
    protected function setDefault($default)
    {
        if (!in_array($this->type, self::$predefinedTypes) ||
                in_array($this->type, [VariableType::DATA_ARRAY, VariableType::MIXED])) {
            throw new NotSupportedException('Default paramter not supported for'
                    . ' data type [' . $this->type . '] for property'
                    . ' [' . $this->class . '::' . $this->propertyName .
                    '].', $this->class, null, 911013);
        }
        try {
            $default = $this->convertDataType($default);
            if (in_array($this->type, [VariableType::DATE, VariableType::DATETIME])) {
                if (strtotime($default) === false) {
                    throw new ApplicationException('Invalid default value for'
                            . ' date time type for property [' .
                            $this->class . '::' . $this->propertyName . '].', $this->class, null, 911014);
                }
            } else {
                $this->validatePredefined($default);
            }
        } catch (Exception $e) {
            throw new ApplicationException('Invalid default value for property'
                    . ' [' . $this->class . '::' . $this->propertyName .
                    '].', $this->class, null, 911015);
        }

        $this->default = $default;
        return $this;
    }

    /**
     * Converts data type of value to be used in default value.
     * 
     * @param string $value
     * @return mixed
     */
    private function convertDataType($value)
    {
        switch ($this->type) {
            case VariableType::INT:
                return filter_var($value, FILTER_VALIDATE_INT) ? (int) $value : $value;
            case VariableType::DOUBLE:
            case VariableType::FLOAT:
                return filter_var($value, FILTER_VALIDATE_FLOAT) ? doubleval($value) : $value;
            case VariableType::STRING:
                return (string) $value;
            case VariableType::BOOLEAN:
            case VariableType::BOOL:
                return is_bool($value) ? $value : null;
            case VariableType::DATE:
            case VariableType::DATETIME:
                return $value;
            default:
                return null;
        }
    }

}
