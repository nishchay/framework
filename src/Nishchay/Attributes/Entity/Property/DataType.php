<?php

namespace Nishchay\Attributes\Entity\Property;

use DateTime;
use Nishchay\Exception\InvalidAttributeException;
use Nishchay\Exception\ApplicationException;
use Nishchay\Exception\NotSupportedException;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Utility\MethodInvokerTrait;
use Nishchay\Processor\VariableType;

/**
 * Description of DataType
 *
 * @author bhavik
 */
#[\Attribute]
class DataType
{

    use AttributeTrait {
        verify as parentVerify;
    }

    use MethodInvokerTrait;

    /**
     * Property name on which attribute is defined.
     * 
     * @var string 
     */
    private $propertyName;

    /**
     * Predefined data types.
     * 
     * @var array 
     */
    const PREDEFINED_TYPES = [
        VariableType::INT, VariableType::FLOAT, VariableType::DOUBLE,
        VariableType::STRING, VariableType::DATE, VariableType::DATETIME,
        VariableType::DATA_ARRAY, VariableType::MIXED, VariableType::BOOLEAN
    ];
    const NAME = 'dataType';

    public function __construct(private string $type,
            private ?int $length = null, private bool $required = false,
            private bool $readOnly = false, private bool $encrypt = false,
            private array $values = [], private $default = null)
    {
        
    }

    /**
     * Sets property name
     * @param string $propertyName
     */
    public function setPropertyName(string $propertyName)
    {
        if ($this->propertyName !== null) {
            return null;
        }

        $this->propertyName = $propertyName;
    }

    public function verify()
    {
        $this->parentVerify();
        $this->refactorType()
                ->refactorValues()
                ->refactorDefault();
    }

    /**
     * 
     */
    protected function refactorType()
    {
        $this->type = strtolower($this->type);
        #Class should be exist when type is not primitive.
        if (!in_array($this->type, self::PREDEFINED_TYPES) && !class_exists($this->type)) {
            throw new InvalidAttributeException('Invalid data type'
                            . ' [' . $this->type . '] for property [' .
                            $this->class . '::' . $this->propertyName . '].',
                            $this->class, null, 911003);
        }

        if (trim($this->type, '\\') === DateTime::class) {
            throw new InvalidAttributeException('Use date or'
                            . ' datetime as data type instead of DateTime class for property'
                            . ' [' . $this->class . '::' . $this->propertyName . ']',
                            $this->class, null, 911004);
        }

        return $this;
    }

    /**
     * Returns all pre defined data types.
     * 
     * @return array
     */
    public static function getPreDefinedTypes()
    {
        return self::PREDEFINED_TYPES;
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
            $this->readOnly && $this->checkReadonly($set);

            return true;
        }

        # Now we should check that property is readonly or not.
        $this->readOnly && $this->checkReadonly($set);

        $this->validatePredefined($value) || $this->validateUserDefined($value);

        if (!empty($this->values) && in_array($value, $this->values) === false) {
            throw new ApplicationException('Value of property [' .
                            $this->class . '::' . $this->propertyName . ']'
                            . ' should form values list.', $this->class, null,
                            911007);
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
                            $this->propertyName . '] is readonly.',
                            $this->class, null, 911006);
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
        if (in_array($this->type, self::PREDEFINED_TYPES)) {
            if ($this->invokeMethod(
                            [$this, 'isValid' . ucfirst($this->type)], [$value]
                    ) === false) {
                throw new ApplicationException('Value of property'
                                . ' [' . $this->class . '::' . $this->propertyName . '] is not'
                                . ' valid or not ' . $this->type . '.',
                                $this->class, null, 911008);
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
                            . ' of ' . $this->type . '.', $this->class, null,
                            911009);
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
                            . ' [' . $this->class . '::' . $this->propertyName . '].',
                            $this->class, null, 911010);
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
        return in_array($this->type, self::PREDEFINED_TYPES);
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
     * @param array $this->values
     * @return $this
     */
    protected function refactorValues()
    {
        if (empty($this->values)) {
            return $this;
        }

        if (!in_array($this->type, self::PREDEFINED_TYPES) ||
                in_array($this->type,
                        [VariableType::BOOLEAN, VariableType::DATE, VariableType::DATETIME, VariableType::DATA_ARRAY, VariableType::MIXED])) {
            throw new NotSupportedException('Values parameter not supported for'
                            . ' data type [' . $this->type . '] for property'
                            . ' [' . $this->class . '::' . $this->propertyName .
                            '].', $this->class, null, 911011);
        }

        foreach ($this->values as $key => $value) {
            try {
                $value = $this->convertDataType($value);
                $this->validatePredefined($value);
                $this->values[$key] = $value;
            } catch (Exception $e) {
                throw new ApplicationException('Invalid item in values parameter for property'
                                . ' [' . $this->class . '::' . $this->propertyName .
                                '].', $this->class, null, 911012);
            }
        }
        return $this;
    }

    /**
     * Sets default value.
     * 
     * @param string $this->default
     * @return $this
     */
    protected function refactorDefault()
    {
        if ($this->default === null) {
            return $this;
        }

        if (!in_array($this->type, self::PREDEFINED_TYPES) ||
                in_array($this->type,
                        [VariableType::DATA_ARRAY, VariableType::MIXED])) {
            throw new NotSupportedException('Default paramter not supported for'
                            . ' data type [' . $this->type . '] for property'
                            . ' [' . $this->class . '::' . $this->propertyName .
                            '].', $this->class, null, 911013);
        }
        try {
            $this->default = $this->convertDataType($this->default);
            if (in_array($this->type,
                            [VariableType::DATE, VariableType::DATETIME])) {
                if (strtotime($this->default) === false) {
                    throw new ApplicationException('Invalid default value for'
                                    . ' date time type for property [' .
                                    $this->class . '::' . $this->propertyName . '].',
                                    $this->class, null, 911014);
                }
            } else {
                $this->validatePredefined($this->default);
            }
        } catch (Exception $e) {
            throw new ApplicationException('Invalid default value for property'
                            . ' [' . $this->class . '::' . $this->propertyName .
                            '].', $this->class, null, 911015);
        }


        return $this;
    }

    /**
     * Converts data type of value to be used in default value.
     * 
     * @param string $value
     * @return mixed
     */
    public function convertDataType($value)
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
